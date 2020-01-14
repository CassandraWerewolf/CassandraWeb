<?php 

    eaccelerator_clear();
<!-- {{{ information -->
<h2>Information</h2>
<table>
<tr>
    <td class="e">Caching enabled</td> 
    <td><?php echo $info['cache'] ? 'yes':'no' ?></td>
</tr>
<tr>
    <td class="e">Optimizer enabled</td>
    <td><?php echo $info['optimizer'] ? 'yes':'no' ?></td>
</tr>
<tr>
    <td class="e">Memory usage</td>
    <td><?php echo number_format(100 * $info['memoryAllocated'] / $info['memorySize'], 2); ?>% 
        (<?php echo number_format($info['memoryAllocated'] / (1024*1024), 2); ?>MB/
        <?php echo number_format($info['memorySize'] / (1024*1024), 2); ?>MB)</td>
</tr>
<tr>
    <td class="e">Free memory</td>
    <td><?php echo number_format($info['memoryAvailable'] / (1024*1024), 2); ?>MB</td>
</tr>
<tr>
    <td class="e">Cached scripts</td>
    <td><?php echo $info['cachedScripts']; ?></td>
</tr>
<tr>
    <td class="e">Removed scripts</td> 
    <td><?php echo $info['removedScripts']; ?></td>
</tr>
<tr>
    <td class="e">Cached keys</td>
    <td><?php echo $info['cachedKeys']; ?></td>
</tr>
</table>
<!-- }}} -->

<!-- {{{ control -->
<h2>Actions</h2>
<form name="ea_control" method="post">
    <table>
        <tr>
            <td class="e">Caching</td>
            <td><input type="submit" name="caching" value="<?php echo $info['cache']?'disable':'enable'; ?>" /></td>
        </tr>
        <tr>
            <td class="e">Optimizer</td>
            <td><input type="submit" name="optimizer" value="<?php echo $info['optimizer']?'disable':'enable'; ?>" /></td>
        </tr>
        <tr>
            <td class="e">Clear cache</td>
            <td><input type="submit" name="clear" value="clear" title="remove all unused scripts and data from shared memory and disk cache" /></td>
        </tr>
        <tr>
            <td class="e">Clean cache</td>
            <td><input type="submit" name="clean" value="clean" title=" remove all expired scripts and data from shared memory and disk cache" /></td>
        </tr>
        <tr>
            <td class="e">Purge cache</td>
            <td><input type="submit" name="purge" value="purge" title="remove all 'removed' scripts from shared memory" /></td>
        </tr>
    </table>
</form>
<!-- }}} -->

<h2>Cached scripts</h2>
<?php create_script_table(eaccelerator_cached_scripts()); ?>

<h2>Removed scripts</h2>
<?php create_script_table(eaccelerator_removed_scripts()); ?>

<?php
if (function_exists('eaccelerator_get')) {
    echo "<h2>Cached keys</h2>";
    create_key_table(eaccelerator_list_keys());
}
?>

<!-- {{{ footer -->
<br /><br />
<table>
    <tr><td class="center">
    <a href="http://eaccelerator.net"><img src="?=<?php echo $info['logo']; ?>" alt="eA logo" /></a>
    <strong>Created by the eAccelerator team, <a href="http://eaccelerator.net">http://eaccelerator.net</a></strong><br /><br />
    <nobr>eAccelerator <?php echo $info['version']; ?> [shm:<?php echo $info['shm_type']?> sem:<?php echo $info['sem_type']; ?>]</nobr><br />
    <nobr>PHP <?php echo phpversion();?> [ZE <?php echo zend_version(); ?>]</nobr><br />
    <nobr>Using <?php echo php_sapi_name();?> on <?php echo php_uname(); ?></nobr><br />
    </td></tr>
</table>
<!-- }}} -->
</body>
</html>

<?php

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */

?>
