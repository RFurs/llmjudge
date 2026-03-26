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
namespace qbank_llmjudge;

use core_question\local\bank\column_base;
use html_writer;
use moodle_url;

/**
 * Class responsible for extending qbank columns
 */
class score_column extends column_base {
    /**
     * Returns the internal name of the column.
     *
     * @return string Column identifier
     */
    public function get_name(): string {
        return 'llm_score';
    }
    /**
     * Returns the column header title.
     *
     * @return string Localised column title
     */
    public function get_title(): string {
        return get_string('llmscore', 'qbank_llmjudge');
    }

    /**
     * Join the evaluation table to the main question query.
     * We use a subquery to aggregate the max score and most recent date per question.
     */
    public function get_extra_joins(): array {
        return [
            'llm' => "LEFT JOIN (
                SELECT questionid,
                       MAX(overallscore) AS maxscore,
                       MAX(timecreated) AS latest_eval
                FROM {qbank_llmjudge_eval}
                GROUP BY questionid
            ) llm ON llm.questionid = q.id",
        ];
    }

    /**
     * Make these fields available in the $question object.
     */
    public function get_required_fields(): array {
        return [
            'llm.maxscore',
            'llm.latest_eval',
        ];
    }

    /**
     * Define which fields can be used for sorting the column.
     */
    public function is_sortable() {
        return [
            'maxscore' => ['field' => 'llm.maxscore', 'title' => get_string('maxscore', 'qbank_llmjudge')],
            'timecreated' => ['field' => 'llm.latest_eval', 'title' => get_string('timecreated', 'qbank_llmjudge')],
        ];
    }

    /**
     * Outputs the column content using the pre-fetched data.
     */
    protected function display_content($question, $rowclasses): void {
        global $PAGE;

        if (empty($question->maxscore)) {
            echo '-';
            return;
        }

        $courseid = $PAGE->course->id ?? 0;
        $returnurl = $this->qbank->returnurl;

        if ($returnurl === '/') {
            $courseid = $PAGE->course->id ?? 0;
            $returnurl = new moodle_url("/question/edit.php", ['courseid' => $courseid]);
        }

        $url = new \moodle_url('/question/bank/llmjudge/view_evaluations.php', [
            'questionid' => $question->id,
            'courseid' => $courseid,
            'returnurl' => $returnurl,
        ]);

        $scoretext = number_format($question->maxscore, 2);
        $date = userdate($question->latest_eval, get_string('strftimedatetime', 'langconfig'));

        echo html_writer::start_div('llm-score-container');

        echo html_writer::link($url, $scoretext, [
            'class' => 'badge badge-info p-2',
            'title' => get_string('viewdetails', 'qbank_llmjudge'),
            'style' => 'font-size: 0.8em; min-width: 30px;',
        ]);

        echo html_writer::empty_tag('br');

        echo html_writer::tag('span', $date, [
            'class' => 'date',
        ]);

        echo html_writer::end_div();
    }
}
