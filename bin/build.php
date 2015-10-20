#!/usr/bin/env php
<?php
$wd = sys_get_temp_dir();
$sourceDir  = realpath(__DIR__ . "/../..");
$vendorDir  = "$sourceDir/vendor";
$binDir     = "$vendorDir/bin";
$srcDir     = "$sourceDir/src";
$bundle     = "bundle.phar";
$targetDir  = "$sourceDir/target";
$bundleFile = "bundle.phar";
$bundle     = "$targetDir/$bundleFile";

if (!file_exists($targetDir)) {
    if (!mkdir($targetDir)) {
        die("Could not create target dir $targetDir");
    }
}
if (file_exists($bundle)) {
    unlink($bundle);
}
$phar = new Phar($bundle, 0, $bundleFile);
$iterator = new AppendIterator;
$iterator->append(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
    )
);
$iterator->append(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($vendorDir, FilesystemIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
    )
);
$phar->buildFromIterator($iterator, $sourceDir);
$phar->setStub($phar->createDefaultStub("vendor/phasty/service/bin/service.php", "vendor/phasty/service/bin/service.php"));
