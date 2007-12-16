<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('initialize a new dao with S2Base');
pake_task('s2_tool_pager', 'module_exists');
pake_alias('s2pager', 's2_tool_pager');

function run_s2_tool_pager($task, $args) {
    $pluginName = sfS2BaseToolPluginConfig::PLUGIN_NAME;
    run_s2_init_dao($task, $args);

    $appName = $args[0];
    $moduleName = $args[1];
    $tableNames = preg_split('/,/', $args[2]);
    $tableName = $tableNames[0];
    $tableCamelizedName = ucfirst(sfS2BasePlugin_util_camelize($tableName));
    $daoInterfaceName = isset($args[4]) ? $args[4] : $tableCamelizedName . 'Dao';
    $entityClassName = (isset($args[4]) ? preg_replace('/Dao$/', '', $daoInterfaceName) : $tableCamelizedName) . 'Entity';
    $daoPropertyName  = sfS2BasePlugin_util_lcfirst($daoInterfaceName);
    $actionName       = isset($args[5]) ? $args[5] : sfS2BasePlugin_util_lcfirst($tableCamelizedName);
    $actionClassName  = $actionName . 'Action';
    $dtoClassName     = $actionClassName . 'Dto';
    $env = isset($args[3]) ? $args[3] : 'prod';

    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BaseToolPlugin s2_tool_pager task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('Environment    : ' . $env);
    sfS2BasePlugin_util_echo_comment("Application    : $appName");
    sfS2BasePlugin_util_echo_comment("Module         : $moduleName");
    sfS2BasePlugin_util_echo_comment("Action Name    : $actionName");
    sfS2BasePlugin_util_echo_comment("Table Name     : $tableName");
    sfS2BasePlugin_util_echo_comment("Dao Interface  : $daoInterfaceName");
    $tableInfo = sfS2BasePlugin_util_getTableInfoFromPdoDicon($env);
    sfS2BasePlugin_util_echo_comment('Tables in DB   : ' . implode(', ', array_keys($tableInfo)));
    if (!array_key_exists($tableName, $tableInfo)) {
        throw new Exception("table not found. [$name]");
    } else {
        $columns = $tableInfo[$tableName];
        $properties = array();
        foreach($columns as $col) {
            $properties[] = sfS2BasePlugin_util_camelize($col);
        }
    }
    sfS2BasePlugin_util_echo_comment('Columns        : ' . implode(', ', $columns));
    sfS2BasePlugin_util_echo_comment('');

    $app_dir      = sfConfig::get('sf_app_dir') . $appName;
    $module_dir   = $app_dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    $action_dir   = $module_dir . DIRECTORY_SEPARATOR . 'actions';
    $template_dir = $module_dir . DIRECTORY_SEPARATOR . 'templates';
    $dao_dir      = $module_dir . DIRECTORY_SEPARATOR . 'dao';
    $entity_dir   = $module_dir . DIRECTORY_SEPARATOR . 'entity';
    $validate_dir = $module_dir . DIRECTORY_SEPARATOR . 'validate';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir   = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons' . DIRECTORY_SEPARATOR . 's2_tool_pager';

    /** create action class file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'action.tpl');
    $contents = preg_replace(
                    array('/@@DAO_INTERFACE_NAME@@/',
                          '/@@ACTION_CLASS_NAME@@/',
                          '/@@DAO_PROPERTY_NAME@@/',
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@CONDITION_DTO_SESSION_KEY@@/",
                          "/@@ACTION_NAME@@/"),
                    array($daoInterfaceName,
                          $actionClassName,
                          $daoPropertyName,
                          $dtoClassName,
                          $dtoClassName,
                          $actionName),
                    $contents);
    $path     = $action_dir . DIRECTORY_SEPARATOR . $actionClassName . '.class.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);

    /** create template file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'html.tpl');
    $contents = preg_replace(
                    array('/@@MODULE_NAME@@/',
                          '/@@ACTION_NAME@@/',
                          '/@@PROPERTY_ROWS_TITLE@@/',
                          '/@@PROPERTY_ROWS@@/'),
                    array($moduleName,
                          $actionName,
                          run_s2_tool_pager_getPropertyRowsTitle($properties),
                          run_s2_tool_pager_getPropertyRows($properties)),
                    $contents);
    $path     = $template_dir . DIRECTORY_SEPARATOR . $actionName . 'Success.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);

    /** create dto class file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dto.tpl');
    $contents = preg_replace(
                    array('/@@CONDITION_DTO_NAME@@/'),
                    array($dtoClassName),
                    $contents);
    $path     = $entity_dir . DIRECTORY_SEPARATOR . $dtoClassName. '.class.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);

    /** create validate file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'validate.tpl');
    $path     = $validate_dir . DIRECTORY_SEPARATOR . $actionName . '.yml';
    sfS2BasePlugin_util_filePutContents($path, $contents, true);

    /** create dao interface file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao.tpl');
    $contents = preg_replace(
                    array('/@@DAO_INTERFACE_NAME@@/',
                          '/@@ENTITY_CLASS_NAME@@/',
                          '/@@CONDITION_DTO_NAME@@/'),
                    array($daoInterfaceName,
                          $entityClassName,
                          $dtoClassName),
                    $contents);
    $path     = $dao_dir . DIRECTORY_SEPARATOR . $daoInterfaceName . '.class.php';
    sfS2BasePlugin_util_filePutContents($path, $contents, true);

    /** create dao sql file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao_sql.tpl');
    $contents = preg_replace(
                    array("/@@TABLE_NAME@@/",
                          "/@@WHERE_CONDITION@@/"),
                    array($tableName,
                          run_s2_tool_pager_getWhereCondition($columns)),
                    $contents);
    $path     = $dao_dir . DIRECTORY_SEPARATOR . $daoInterfaceName . '_findByConditionDtoList.sql';
    sfS2BasePlugin_util_filePutContents($path, $contents, true);

    sfS2BasePlugin_util_echo_comment('cache clear.');
    run_clear_cache($task, array($appName));

    /** prepare dao-pager.dicon */
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao-pager.dicon',
         sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dao-pager.dicon');

    /** add S2ContainerApplicationContext setting */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'include.tpl');
    $contents = preg_replace(
                    array('/@@MODULE_NAME@@/'),
                    array($moduleName),
                    $contents);
    $path     = $module_dir . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR . $actionName . '.inc.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);
}

function run_s2_tool_pager_getPropertyRowsTitle($properties) {
    $src = '<tr>' . PHP_EOL;
    foreach ($properties as $prop) {
        $src .= '<th>';
        $src .= ucfirst($prop);
        $src .= '</th>' . PHP_EOL;
    }
    return $src . '</tr>' . PHP_EOL;
}

function run_s2_tool_pager_getPropertyRows($properties) {
    $src = '<tr>' . PHP_EOL;
    foreach ($properties as $prop) {
        $src .= '<td>';
        $src .= '<?php echo $row->get' . ucfirst($prop) . '(); ?>';
        $src .= '</td>' . PHP_EOL;
    }
    return $src . '</tr>' . PHP_EOL;
}

function run_s2_tool_pager_getWhereCondition($cols) {
    foreach($cols as $col) {
        $conds[] = "      $col like /*dto.keywordLike*/'%%'";
    }
    return implode(' or' . PHP_EOL, $conds);
}
