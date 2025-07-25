<?php

class block_stickynotes extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_stickynotes');
    }
// Main function to render block content
public function get_content() {
    global $USER, $OUTPUT, $DB;
 // Return cached content if already set
    if ($this->content !== null) {
        return $this->content;
    }

    require_once(__DIR__ . '/block_stickynotes_form.php');

    $this->content = new stdClass();
    $this->content->text = '';
    $this->content->footer = '';
 // Check if we are editing an existing note
    $editing = null;
    $editid = optional_param('edit', 0, PARAM_INT);
    if ($editid) {
        $editing = $DB->get_record('block_stickynotes', ['id' => $editid, 'userid' => $USER->id]);
    }

    $mform = new block_stickynotes_form(null, ['editing' => $editing]);
 // Handle form submission
    if ($mform->is_cancelled()) {
        // Redirect if form is cancelled
        redirect(new moodle_url('/my'));
    } else if ($fromform = $mform->get_data()) {
         // If editing existing note
        if (!empty($fromform->id)) {
            $record = $DB->get_record('block_stickynotes', ['id' => $fromform->id, 'userid' => $USER->id]);
            if ($record) {
                $record->note = $fromform->note;
                $record->duedate = $fromform->duedate;
                $record->timemodified = time();
                $DB->update_record('block_stickynotes', $record);
            }
        } else {
            // If adding a new note
            $record = new stdClass();
            $record->userid = $USER->id;
            $record->note = $fromform->note;
            $record->duedate = $fromform->duedate;
            $record->timecreated = time();
            $record->timemodified = time();
            $DB->insert_record('block_stickynotes', $record);
        }
        redirect(new moodle_url('/my'));
    }
// Display the form (output buffering)
    ob_start();
    $mform->display();
    $this->content->text .= ob_get_clean();

    // Styling
    $this->content->text .= '<style>
        .stickynote {
            background-color: #ffffff;
            border-left: 8px solid #ffffff;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-family: "Segoe UI", sans-serif;
            position: relative;
        }
        .stickynote.overdue {
            background-color: #ffebee;
            border-left-color: #e53935;
        }
        .stickynote.today {
            background-color: #fff9c4;
            border-left-color: #fbc02d;
        }
        .stickynote .note-actions {
            position: absolute;
            top: 8px;
            right: 12px;
        }
        .stickynote .note-actions a {
            margin-left: 10px;
            font-size: 16px;
            text-decoration: none;
        }
        .reminder {
            color: #d84315;
            font-weight: 600;
            margin-top: 8px;
        }
    </style>';

    // Notes
    $notes = $DB->get_records('block_stickynotes', ['userid' => $USER->id]);
    foreach ($notes as $note) {
        $duedateText = userdate($note->duedate);
        $editurl = new moodle_url('/my', ['edit' => $note->id]);
        $deleteurl = new moodle_url('/blocks/stickynotes/delete.php', ['id' => $note->id]);

        $editlink = html_writer::link($editurl, '✏️');
        $deletelink = html_writer::link($deleteurl, '🗑️', [
            'onclick' => "return confirm('Are you sure you want to delete this note?');"
        ]);

        $noteDate = userdate($note->duedate, '%Y-%m-%d');
        $today = userdate(time(), '%Y-%m-%d');

        $classes = 'stickynote';
        $reminder = '';
        if ($noteDate === $today) {
            $classes .= ' today';
            $reminder = '<div class="reminder">You have a note due today!</div>';
        } elseif ($note->duedate < time()) {
            $classes .= ' overdue';
        }
// Render each note with styling, actions, and reminder if due today
        $this->content->text .= html_writer::start_div($classes);
        $this->content->text .= html_writer::start_div('note-actions');
        $this->content->text .= $editlink . $deletelink;
        $this->content->text .= html_writer::end_div();
        $this->content->text .= html_writer::tag('div', s($note->note));
        $this->content->text .= html_writer::tag('div', "(Due: {$duedateText})", ['style' => 'margin-top: 5px; font-size: 0.9em; color: #555;']);
        $this->content->text .= $reminder;
        $this->content->text .= html_writer::end_div();
    }

    return $this->content;
}
}
