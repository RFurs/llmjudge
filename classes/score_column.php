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

/**
 * Class responsible for extending qbank columns
 */
class score_column extends column_base {
    public function get_name(): string {
        return 'llm_score';
    }

    public function get_title(): string {
        return get_string('llmscore', 'qbank_llmjudge');
    }

    protected function display_content($question, $rowclasses): void {
        global $DB, $OUTPUT, $PAGE;

        $record = $DB->get_record_sql(
            "SELECT overallscore
            FROM {qbank_llmjudge_eval}
            WHERE questionid = :questionid
            ORDER BY timecreated DESC",
            ['questionid' => $question->id],
            IGNORE_MULTIPLE,
        );

        if (!$record) {
            echo '-';
            return;
        }

        $courseid = $PAGE->course->id ?? 0;

        $url = new \moodle_url('/question/bank/llmjudge/view_evaluations.php', [
            'questionid' => $question->id,
            'courseid' => $courseid,
        ]);

        $scoretext = number_format($record->overallscore, 2);

        echo \html_writer::link($url, $scoretext, [
            'class' => 'badge badge-info',
            'title' => get_string('viewdetails', 'qbank_llmjudge'),
        ]);
    }
}
