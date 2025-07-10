<?php

require_once('../../config.php');
require_login();

$id = required_param('id', PARAM_INT);

// Check if the note exists and belongs to the current user.
$note = $DB->get_record('block_stickynotes', ['id' => $id, 'userid' => $USER->id], '*', IGNORE_MISSING);

if ($note) {
    $DB->delete_records('block_stickynotes', ['id' => $id]);
}

// Redirect back to dashboard.
redirect(new moodle_url('/my'));
