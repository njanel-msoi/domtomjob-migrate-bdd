<?php include_once 'export-functions.php' ?>
<div class="wrap">
    <h1>DTJ Dabatase migration plugin</h1>
    <p>Use this tool to migrate dbb from a server to another<br>
        Exemple : from dev.domtomjob.com to preprod.domtomjob.com</p>

    <?php $all_table_names = get_table_names(); ?>
    <?php
    $source = isset($_POST['source']) ? $_POST['source'] : $_SERVER['SERVER_NAME'];
    $target = isset($_POST['target']) ? $_POST['target'] : 'dev.domtomjob.com';
    $isHttps = isset($_POST['go']) ? (isset($_POST['ishttps']) ? $_POST['ishttps'] : '') : 'on';

    $tables_selected = isset($_POST['tables']) ? $_POST['tables'] : $all_table_names;
    ?>

    <div style="display: flex; width: 100%;">
        <div style="flex: 1">
            <form method="POST">
                <input type="hidden" name="go" value="1">
                <label>Source server <input required name="source" value="<?= $source ?>"></label><br>
                <label>Target server <input required name="target" value="<?= $target ?>"></label><br>
                <label>Target HTTPS <input type="checkbox" name="ishttps" <?php if ($isHttps == 'on') echo 'checked'; ?>></label>
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