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
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('qbank/llmjudge:evaluate', $context);

$question = $DB->get_record('question', ['id' => $questionid], '*', MUST_EXIST);
$evaluations = $DB->get_records('qbank_llmjudge_eval', ['questionid' => $questionid], 'timecreated DESC');

$PAGE->set_url(new moodle_url('/question/bank/llmjudge/view_evaluations.php', [
    'questionid' => $questionid,
    'courseid' => $courseid,
    'returnurl' => $returnurl,
]));
$PAGE->set_title(get_string('evaluations', 'qbank_llmjudge'));
$PAGE->set_heading(get_string('evaluations', 'qbank_llmjudge'));

echo $OUTPUT->header();

$formattedquestion = format_text($question->questiontext, $question->questiontextformat, ['context' => $context]);

$answershtml = '';
if ($question->qtype !== 'essay') {
    $answers = $DB->get_records('question_answers', ['question' => $questionid], 'id ASC');
    if ($answers) {
        $answershtml .= html_writer::tag('hr', '');
        $answershtml .= html_writer::tag('strong', get_string('answers', 'question') . ':');
        $answershtml .= html_writer::start_tag('ul', ['class' => 'list-unstyled mt-2']);

        foreach ($answers as $ans) {
            $iscorrect = ($ans->fraction > 0);
            $badgestyle = $iscorrect ? 'badge-success' : 'badge-light border';
            $icon = $iscorrect ? '<i class="fa fa-check text-success"></i> ' : '';

            $anstext = format_text($ans->answer, $ans->answerformat, ['context' => $context]);
            $bgstyle = $iscorrect ? 'background-color:#e9f7ef;' : '';
            $answershtml .= html_writer::tag(
                'li',
                html_writer::div(
                    $icon . $anstext,
                    ' p-2 mb-1 rounded ' . ($iscorrect ? 'border-left border-success' : 'bg-white border-left'),
                    ['style' => 'border-width: 4px !important; ' . $bgstyle]
                )
            );
        }
        $answershtml .= html_writer::end_tag('ul');
    }
}

$getcriterionlabel = function (string $criterionname): string {
    $manager = get_string_manager();
    if ($manager->string_exists($criterionname, 'qbank_llmjudge')) {
        return get_string($criterionname, 'qbank_llmjudge');
    }
    return $criterionname;
};

echo html_writer::start_div('row');

echo html_writer::start_div('col-lg-4 col-md-12 mb-4');
echo html_writer::start_div('card sticky-top', ['style' => 'top: 80px; z-index: 10;']);
echo html_writer::div('<strong>' . get_string('question') . ': ' . s($question->name) . '</strong>', 'card-header bg-light');
echo html_writer::div($formattedquestion . $answershtml, 'card-body small overflow-auto', ['style' => 'max-height: 80vh;']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('col-lg-8');

if (!$evaluations) {
    echo $OUTPUT->notification(get_string('noevaluationsfound', 'qbank_llmjudge'), 'info');
} else {
    echo html_writer::start_div('list-group shadow-sm');

    foreach ($evaluations as $eval) {
        $data = json_decode($eval->rawjson, true);

        $currenteval = null;
        foreach ($data['evaluations'] ?? [] as $e) {
            if ($e['question_id'] == $questionid) {
                $currenteval = $e;
                break;
            }
        }

        $id = 'tabs-' . $eval->id;

        $overallscore = $eval->overallscore;
        if ($overallscore == 1) {
            $overallbadgeclass = 'badge-success';
            $overallbgstyle = 'background-color:#4d885e;color:#fff;';
        } else if ($overallscore >= 0.5) {
            $overallbadgeclass = 'badge-warning';
            $overallbgstyle = 'background-color:#b7791f;color:#fff;';
        } else {
            $overallbadgeclass = 'badge-danger';
            $overallbgstyle = 'background-color:#af4040;color:#fff;';
        }

        echo html_writer::start_div('list-group-item');

        echo "<div class='d-flex justify-content-between align-items-start flex-wrap gap-2'>";
        echo "<div class='me-3'>";
        echo "<div><code>" . s($eval->model) . "</code></div>";
        echo "<small class='text-muted'>" . userdate($eval->timecreated, get_string('strftimedatetime', 'langconfig')) . "</small>";
        echo "</div>";

        echo "<div class='text-end'>";
        echo html_writer::tag('span', get_string('overallscore', 'qbank_llmjudge') . ': ' . s((string)$overallscore), [
            'class' => "badge {$overallbadgeclass} p-2 shadow-sm",
            'style' => 'font-size:0.9rem; ' . $overallbgstyle . ' color:#fff;',
        ]);
        echo "</div>";
        echo "</div>";

        echo "<ul class='nav nav-tabs mt-3' id='" . s($id) . "-nav' role='tablist'>";

        $first = true;
        foreach ($currenteval['criteria'] ?? [] as $criterionname => $c) {
            $active = $first ? 'active' : '';
            $label = $getcriterionlabel($criterionname);
            $score = $c['score'] ?? '';

            echo "<li class='nav-item' role='presentation'>";
            echo "<a class='nav-link {$active}' data-bs-toggle='tab' href='#"
                . s($id . '-' . $criterionname)
                . "' role='tab' aria-controls='"
                . s($id . '-' . $criterionname)
                . "' aria-selected='"
                . ($first ? 'true' : 'false') . "'>";
            echo s($label) . " <span class='badge bg-info ms-1'>" . s((string)$score) . "</span>";
            echo "</a>";
            echo "</li>";

            $first = false;
        }

        echo "</ul>";

        echo "<div class='tab-content border border-top-0 p-3 bg-white'>";

        $first = true;
        foreach ($currenteval['criteria'] ?? [] as $criterionname => $c) {
            $active = $first ? 'show active' : '';
            $feedback = s($c['feedback'] ?? '');
            $suggestion = s($c['suggestion'] ?? '');

            echo "<div class='tab-pane fade {$active}' id='" . s($id . '-' . $criterionname) . "' role='tabpanel'>";

            echo "<div class='mt-2'><strong>" . get_string('feedback', 'qbank_llmjudge') . ":</strong><br>{$feedback}</div>";
            if ($suggestion !== '') {
                echo "<div class='mt-2 text-success'><strong>" . get_string('suggestions', 'qbank_llmjudge') . ":</strong><br>{$suggestion}</div>";
            }

            echo "</div>";

            $first = false;
        }

        echo "</div>";

        echo html_writer::end_div();
    }

    echo html_writer::end_div();
}

echo html_writer::end_div();

if (!empty($returnurl)) {
    echo $OUTPUT->continue_button(new moodle_url($returnurl));
} else {
    echo $OUTPUT->continue_button(new moodle_url('/question/edit.php', ['courseid' => $courseid]));
}

echo $OUTPUT->footer();
