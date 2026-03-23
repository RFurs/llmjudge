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

/**
 * Class responsible for saving evaluation results in DB
 */
class evaluation_saver {
    /**
     * Saves LLM evaluation in mdl_qbank_llmjudge_eval table
     *
     * @param stdClass contains evaluation data
     * @return void
     * @throws \moodle_exception
     */
    public static function save_evaluation(\stdClass $evaldata) {
        global $DB;

        $timecreated = time();
        $data = $evaldata->json;

        $overallscore = 0;
        $criterioncount = 0;
        foreach ($data['evaluations'] as $eval) {
            $questionid = (int) ($eval['question_id'] ?? 0);

            foreach ($eval['criteria'] as $criterion) {
                if (isset($criterion['score']) && $criterion['score'] !== null) {
                    $criterioncount++;
                    $overallscore += (int)$criterion['score'];
                }
            }
            $overallscore = ($criterioncount > 0) ? ($overallscore / $criterioncount) : 0;

            $record = (object)[
                'questionid' => $questionid,
                'contextid' => $evaldata->contextid,
                'userid' => $evaldata->userid,
                'timecreated' => $timecreated,
                'overallscore' => $overallscore,
                'model' => $evaldata->model,
                'rawjson' => json_encode($evaldata->json, JSON_PRETTY_PRINT),
            ];

            $DB->insert_record('qbank_llmjudge_eval', $record);
        }
    }
}
