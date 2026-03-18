<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information for qbank_llmjudge.
 *
 * @package    qbank_llmjudge
 * @copyright  2026 Renat Furs <fursrenat@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qbank_llmjudge\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Evaluate form class.
 */
class evaluate_form extends \moodleform {
    /**
     * Defining form structure
     */
    public function definition() {
        global $CFG, $PAGE;
        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];
        $questionlist = $this->_customdata['questionlist'] ?? '';

        $mform->addElement(
            'header',
            'general',
            get_string('pluginname', 'qbank_llmjudge')
        );

        $bloomlevels = [
            'remember' =>
                get_string('remember', 'qbank_llmjudge'),
            'understand' =>
                get_string('understand', 'qbank_llmjudge'),
            'apply' =>
                get_string('apply', 'qbank_llmjudge'),
            'analyze' =>
                get_string('analyze', 'qbank_llmjudge'),
            'evaluate' =>
                get_string('evaluate', 'qbank_llmjudge'),
            'create' =>
                get_string('create', 'qbank_llmjudge'),
        ];

        $mform->addElement(
            'select',
            'cognitive_difficulty',
            get_string('cognitivedifficulty', 'qbank_llmjudge'),
            $bloomlevels
        );

        $educationlevels = [
            'early childhood education' =>
                get_string('earlyeducation', 'qbank_llmjudge'),
            'primary education' =>
                get_string('primaryeducation', 'qbank_llmjudge'),
            'lower secondary education' =>
                get_string('lowersecondaryeducation', 'qbank_llmjudge'),
            'upper secondary education' =>
                get_string('uppersecondaryeducation', 'qbank_llmjudge'),
            'post-secondary non-tertiary education' =>
                get_string('postsecondaryeducation', 'qbank_llmjudge'),
            'short-cycle tertiary education' =>
                get_string('tertiaryeducation', 'qbank_llmjudge'),
            'bachelor or equivalent' =>
                get_string('bachelor', 'qbank_llmjudge'),
            'master or equivalent' =>
                get_string('master', 'qbank_llmjudge'),
            'doctoral or equivalent' =>
                get_string('doctoral', 'qbank_llmjudge'),
        ];

        $mform->addElement(
            'select',
            'education_levels',
            get_string('educationlevel', 'qbank_llmjudge'),
            $educationlevels
        );

        $mform->addElement('hidden', 'courseid');
        $mform->setDefault('courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'movequestionsselected');
        $mform->setType('movequestionsselected', PARAM_RAW);
        $mform->setDefault('movequestionsselected', $questionlist);

        $this->add_action_buttons(
            true,
            get_string('evaluate', 'qbank_llmjudge')
        );
    }
}
