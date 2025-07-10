<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class block_stickynotes_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $editing = $this->_customdata['editing'] ?? null;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $editing->id ?? 0);

        $mform->addElement('textarea', 'note', get_string('note', 'block_stickynotes'), 'wrap="virtual" rows="5" cols="40"');
        $mform->setType('note', PARAM_TEXT);
        $mform->addRule('note', null, 'required', null, 'client');
        $mform->setDefault('note', $editing->note ?? '');

        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'block_stickynotes'));
        $mform->setDefault('duedate', $editing->duedate ?? time());

        $mform->addElement('submit', 'submitbutton', get_string('savenote', 'block_stickynotes'));
    }
}
