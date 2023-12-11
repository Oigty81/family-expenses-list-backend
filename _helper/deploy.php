<?php

$srcDir = getcwd();
$destDir = "";

if(substr($argv[1], -1) == '\\' || substr($argv[1], -1) == '/') {
    $destDir = substr($argv[1], 0, -1);
} else {
    $destDir = $argv[1];
}

echo "deploy from  ".$srcDir."   to ".$destDir."  |\r\n";

$srcDirContentFull = getDirContents($srcDir);
$destDirContentFull = getDirContents($destDir);

$srcDirContent = removeStringPartFromStingsInArray($srcDirContentFull, $srcDir);
$destDirContent = removeStringPartFromStingsInArray($destDirContentFull, $destDir);

// ---------------------------------------------------------------------------
// search for redundant dest files
echo "--> search for redundant dest files \r\n";
foreach($destDirContent as $keyDest=>$valueDest) {
    $foundInner = false;

    foreach($srcDirContent as $keySrc=>$valueSrc) {
        if(strcmp($valueDest, $valueSrc) == 0) {
            $foundInner = true;
            break;
        }
    }

    if($foundInner == false) {
        $fullPath = $destDir.$valueDest;
        if(!is_dir($fullPath)) {
            echo "found... : ".$fullPath."\r\n";
            unlink($fullPath);
        }
    }
}

// ---------------------------------------------------------------------------
// search for existing files with a newer date to overwrite in the dest folder
echo "--> search for existing files with a newer date to overwrite in the dest folder \r\n";
foreach($destDirContent as $keyDest=>$valueDest) {

    foreach($srcDirContent as $keySrc=>$valueSrc) {
        if(strcmp($valueDest, $valueSrc) == 0) {
            $fullPathSrc = $srcDir.$valueSrc;
            $fullPathDest = $destDir.$valueDest;

            if(filemtime($fullPathDest) < filemtime($fullPathSrc)) {
                echo "found... : ".$fullPathSrc."\r\n";
                mycopy($fullPathSrc, $fullPathDest);
            }
            break;
        }
    }
}

// ---------------------------------------------------------------------------
// search for copy new files in dest folder
echo "--> search for copy new files in dest folder \r\n";
foreach($srcDirContent as $keySrc=>$valueSrc) {
    $foundInner = false;

    foreach($destDirContent as $keyDest=>$valueDest) {
        if(strcmp($valueSrc, $valueDest) == 0) {
            $foundInner = true;
            break;
        }
    }

    if($foundInner == false) {
        $fullPathSrc = $srcDir.$valueSrc;
        $fullPathDest = $destDir.$valueSrc; // "$valueSrc" is okay, because "$valueDest" has a wrong value in this scope
        if(!is_dir($fullPathSrc)) {
            echo "found... : ".$fullPathSrc."\r\n";
            mycopy($fullPathSrc,  $fullPathDest);
        }
    }
}

echo "--> Ready \r\n";


// ------------------------------------------------
function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        
        if(str_contains($path, '\\.git\\')) {
            continue;
        }

        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }
    return $results;
}

function removeStringPartFromStingsInArray($srcArray, $searchString) {
    $destArray = [];
    foreach($srcArray as $key=>$value) {
        $result = str_replace($searchString, '', $value);
        array_push($destArray, $result);
    }
    
    return $destArray;
}

function mycopy($s1, $s2) {
    $path = pathinfo($s2);
    if (!file_exists($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }
    if (!copy($s1, $s2)) {
        echo "copy failed \n";
    }
}