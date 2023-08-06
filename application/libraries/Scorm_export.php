<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scorm_export
{

    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function create_scorm_package($course_title, $data_json, $package_name)
    {
        $scorm_dir = sys_get_temp_dir() . '/' . $package_name;
        if (!file_exists($scorm_dir)) {
            mkdir($scorm_dir, 0777, true);
        }

        $data_json_filename = 'data.json';
        $data_json_file = $scorm_dir . '/' . $data_json_filename;
        file_put_contents($data_json_file, $data_json);

        $this->generate_imsmanifest_xml($course_title, $data_json_filename, $scorm_dir);

        $zip_file = $scorm_dir . '.zip';
        $this->zip_directory($scorm_dir, $zip_file);

        return $zip_file;
    }

    protected function generate_imsmanifest_xml($course_title, $data_json_filename, $scorm_dir)
    {
        $manifest_template = '
            <?xml version="1.0" encoding="UTF-8"?>
            <manifest xmlns="http://www.imsglobal.org/xsd/imscp_v1p1"
                xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://www.imsglobal.org/xsd/imscp_v1p1 http://www.imsglobal.org/xsd/imsccv1p1/imsmanifest.xml">
                <metadata>
                    <schema>ADL SCORM</schema>
                    <schemaversion>1.3</schemaversion>
                    <adlcp:location>data.json</adlcp:location>
                </metadata>
                <organizations>
                    <organization identifier="ORG1">
                        <title>' . $course_title . '</title>
                        <item identifier="ITEM1" identifierref="RESOURCE" isvisible="true">
                            <title>Item 1</title>
                        </item>
                    </organization>
                </organizations>
                <resources>
                    <resource identifier="RESOURCE" type="webcontent" adlcp:scormtype="sco" href="' . $data_json_filename . '">
                        <file href="' . $data_json_filename . '" />
                    </resource>
                </resources>
            </manifest>
        ';

        $manifest_file = $scorm_dir . '/imsmanifest.xml';
        file_put_contents($manifest_file, $manifest_template);
    }

    protected function zip_directory($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}
