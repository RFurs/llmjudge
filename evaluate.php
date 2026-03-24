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

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../editlib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $COURSE;

$cmid = optional_param('cmid', 0, PARAM_INT);
$returnurlparam = optional_param('returnurl', 0, PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);
$questionlist = optional_param('movequestionsselected', null, PARAM_RAW);

if ($returnurlparam) {
    $returnurl = new \moodle_url($returnurlparam);
} else {
    if ((int)$CFG->branch >= 500) {
        $returnurl = new \moodle_url('/question/edit.php', ['cmid' => $cmid]);
    } else {
        $returnurl = new \moodle_url('/question/edit.php', ['courseid' => $courseid]);
    }
}

if (!$questionlist) {
    $rawquestions = $_REQUEST;
    [$questionids, $processedlist] = \qbank_bulkmove\helper::process_question_ids($rawquestions);
    $questionlist = $processedlist;
}

if (empty($questionlist)) {
    throw new \moodle_exception('missingquestionsselected', 'qbank_llmjudge');
}

$questionids = explode(',', $questionlist ?? '');

if ($cmid) {
    [$module, $cm] = get_module_from_cmid($cmid);
    require_login($cm->course, false, $cm);
    $context = context_module::instance($cmid);
    $course = get_course($cm->course);
} else if ($courseid) {
    $course = get_course($courseid);
    require_login($courseid, false);
    $context = context_course::instance($courseid);
} else {
    throw new moodle_exception('missingcourseorcmid', 'question');
}

require_capability('qbank/llmjudge:evaluate', $context);

$PAGE->set_url(
    new moodle_url(
        '/question/bank/llmjudge/evaluate.php',
        ['courseid' => $courseid,
        'cmid' => $cmid,
        'movequestionsselected' => $questionlist,
        ],
    )
);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'qbank_llmjudge'));
$PAGE->set_heading($course->fullname);

$mform = new \qbank_llmjudge\form\evaluate_form(null, [
    'courseid' => $courseid,
    'cmid' => $cmid,
    'returnurl' => $returnurl,
    'questionlist' => $questionlist,
]);

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $mform->get_data()) {
    try {
        if (count($questionids) > 10) {
            throw new \moodle_exception('questionscountexceeded', 'qbank_llmjudge');
        }
        if (!empty($data->returnurl)) {
            $returnurl = new \moodle_url($data->returnurl);
        }
        $encoder = new \qbank_llmjudge\questions_encoder();
        $promptbuilder = new \qbank_llmjudge\prompt_builder();
        $llmevaluator = new \qbank_llmjudge\llm_evaluator();

        $jsonquestions = $encoder->encode_questions_to_json($questionlist, $context);
        $prompt = $promptbuilder->build($data, $jsonquestions);
        $evaluationdata = $llmevaluator->evaluate($prompt, $context->id);

        $params = new \stdClass();
        $params->json = $evaluationdata['llmoutput'];
        $params->model = $evaluationdata['model'];
        $params->contextid = $context->id;
        $params->userid = $USER->id;

        \qbank_llmjudge\evaluation_saver::save_evaluation($params);
        \core\notification::success(get_string('evaluationcompleted', 'qbank_llmjudge'));
        redirect($returnurl);
    } catch (\Exception $e) {
        \core\notification::error($e->getMessage());
    }
}
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
