<?php
function getSnippetContent($filename) {
    $content = file_get_contents($filename);
    $content = trim(str_replace(array('<?php', '?>'), '', $content));
    return $content;
}
