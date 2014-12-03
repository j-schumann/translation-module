#!/usr/bin/env php
<?php
/**
 * Imports translations into the database.
 * First argument is the JSON file holding the translation strings.
 *
 * We do not use a console route for this as BjyAuthorize / the ACL will
 * fail when not all required roles for all rules exist. We probably need the
 * translations to add new users (e.g. for mail templates).
 */

require_once 'initApplication.php';

if (empty($argv[1])) {
    die("JSON file name must be given as first argument!\n");
}

$filename = $argv[1];

if (!file_exists($filename) || !is_readable($filename)) {
    die("Given file is not readable!\n");
}

$ts = $application->getServiceManager()->get('TranslationModule\Service\Translation');
$import = $ts->createImport([
    'createLanguages' => true,
    'createModules'   => true,
]);
$result = $import->importFile($filename);
$ts->generateTranslationFiles();
var_dump($result);
echo "imported ".$result['importedTranslations']." translations\n";
