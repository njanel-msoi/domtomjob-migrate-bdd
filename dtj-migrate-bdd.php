<?php
/*
 * Plugin Name: DTJ Migrate BDD
 */
include_once 'export-functions.php';

add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        'Export DB for DTJ', // Title of the page
        'DTJ DB Export', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'dtj-export-bdd.php',
        'dtj_export_plugin'
    );
});
function dtj_export_plugin()
{

?>
    <div class="wrap">
        <h1>DTJ Dabatase migration plugin</h1>
        <p>Use this tool to migrate dbb from a server to another<br>
            Exemple : from dev.domtomjob.com to preprod.domtomjob.com</p>

        <?php $all_table_names = get_table_names(); ?>
        <?php
        $protocol = 'http://';

        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $protocol = 'https://';
        }

        $source = isset($_POST['source']) ? $_POST['source'] : $protocol . $_SERVER['SERVER_NAME'];
        $target = isset($_POST['target']) ? $_POST['target'] : 'https://dev.domtomjob.com';
        // $isHttps = isset($_POST['go']) ? (isset($_POST['ishttps']) ? $_POST['ishttps'] : '') : 'on';

        $tables_selected = isset($_POST['tables']) ? $_POST['tables'] : $all_table_names;
        ?>

        <div style="display: flex; width: 100%;">
            <div style="flex: 1">
                <form method="POST">
                    <input type="hidden" name="go" value="1">
                    <label>Source server <input required name="source" value="<?= $source ?>"></label><br>
                    <label>Target server <input required name="target" value="<?= $target ?>"></label><br>
                    <!-- <label>Target HTTPS <input type="checkbox" name="ishttps" <?php /* if ($isHttps == 'on') echo 'checked';*/ ?>></label> -->
                    <br>
                    <select required name="tables[]" multiple="multiple" style="width: 100%; height: 300px;">
                        <?php foreach ($all_table_names as $t) : ?>
                            <?php $selected = in_array($t, $tables_selected); ?>
                            <option <?= $selected ? 'selected' : '' ?> value="<?= $t ?>"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button>Generate script for target server</button>
                </form>
            </div>
            <div style="flex:2">
                <?php if (isset($_POST['go']) && $_POST['go'] == "1") : ?>
                    <?php $dump = dump_tables($_POST['tables'], $source, $target, $isHttps); ?>
                    <textarea style="width: 100%; height: 100%;"><?= $dump ?></textarea>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
}
