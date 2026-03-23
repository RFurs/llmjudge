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

$questionid = required_param('questionid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('qbank/llmjudge:evaluate', $context);

$PAGE->set_url(new moodle_url('/question/bank/llmjudge/view_evaluations.php', [
    'questionid' => $questionid,
    'courseid' => $courseid,
]));
$PAGE->set_title(get_string('evaluations', 'qbank_llmjudge'));
$PAGE->set_heading(get_string('evaluations', 'qbank_llmjudge'));

echo $OUTPUT->header();

$evaluations = $DB->get_records('qbank_llmjudge_eval', ['questionid' => $questionid], 'timecreated DESC');

if (!$evaluations) {
    echo $OUTPUT->notification(get_string('noevaluationsfound', 'qbank_llmjudge'), 'info');
} else {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable table-align-top';
    $table->head = [
        'ID',
        get_string('model', 'qbank_llmjudge'),
        get_string('time'),
        get_string('score', 'qbank_llmjudge'),
        get_string('details', 'qbank_llmjudge'),
    ];

    foreach ($evaluations as $eval) {
        $data = json_decode($eval->rawjson, true);
        $currenteval = null;
        if (isset($data['evaluations'])) {
            foreach ($data['evaluations'] as $e) {
                if ($e['question_id'] == $questionid) {
                    $currenteval = $e;
                    break;
                }
            }
        }

        $criteriastring = '';
        $feedbackcontent = '';

        if ($currenteval && isset($currenteval['criteria'])) {
            foreach ($currenteval['criteria'] as $criterionname => $c) {
                $score = $c['score'] ?? null;
                $label = get_string($criterionname, 'qbank_llmjudge');

                if ($score === 1) {
                    $badgeclass = 'badge-success';
                    $icon = '<i class="fa fa-check-circle" aria-hidden="true"></i>';
                } else if ($score === 0) {
                    $badgeclass = 'badge-danger';
                    $icon = '<i class="fa fa-times-circle" aria-hidden="true"></i>';
                } else {
                    $badgeclass = 'badge-secondary';
                    $icon = '<i class="fa fa-minus-circle" aria-hidden="true"></i>';
                    $score = '-';
                }

                $criteriastring .= html_writer::tag('span', "{$icon} {$label}: {$score}", [
                    'class' => "badge {$badgeclass} p-2 mb-1 mr-1 shadow-sm",
                    'style' => 'font-size: 0.85rem;',
                ]);

                $feedback = s($c['feedback'] ?? '');
                $suggestion = s($c['suggestion'] ?? '');

                $feedbackcontent .= html_writer::start_div('card mb-2 border-light shadow-sm');
                $feedbackcontent .= html_writer::div(
                    "<strong>{$label}</strong>",
                    "card-header py-1 px-2 bg-light small font-weight-bold",
                );
                $feedbackcontent .= html_writer::start_div('card-body py-2 px-2 small');
                $feedbackcontent .= "<div><span class='text-muted'>Feedback:</span> {$feedback}</div>";
                if ($suggestion) {
                    $feedbackcontent .= "<div class='mt-1 text-success'><i class='fa fa-lightbulb-o'></i> <strong>Suggestion:</strong> {$suggestion}</div>";
                }
                $feedbackcontent .= html_writer::end_div();
                $feedbackcontent .= html_writer::end_div();
            }
        }

        $feedbackbutton = \html_writer::tag(
            'button',
            '<i class="fa fa-search-plus"></i> ' . get_string('viewdetails', 'qbank_llmjudge'),
            [
                'class' => 'btn btn-outline-primary btn-sm btn-block mb-1',
                'data-toggle' => 'collapse',
                'data-target' => '#feedback-' . $eval->id,
            ]
        );

        $feedbackdiv = \html_writer::div(
            $feedbackcontent,
            'collapse mt-2',
            ['id' => 'feedback-' . $eval->id]
        );

        $table->data[] = [
            $eval->id,
            "<code>" . s($eval->model) . "</code>",
            userdate($eval->timecreated),
            $criteriastring,
            $feedbackbutton . $feedbackdiv,
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->continue_button(new moodle_url('/question/edit.php', ['courseid' => $courseid]));
echo $OUTPUT->footer();
