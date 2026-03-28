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

use core_ai\manager;
use core_ai\aiactions\generate_text;

/**
 * Class responsible for sending a request to AI subsystem
 */
class llm_evaluator {
    /**
     * Function responsible for generating an evaluation using AI subsystem
     * @param string The fully constructed prompt
     * @param int Course context ID
     * @return string JSON evaluation
     */
    public function evaluate(string $prompt, int $contextid) {
        global $USER, $CFG;

        $action = new \core_ai\aiactions\generate_text(
            contextid: $contextid,
            userid: $USER->id,
            prompttext: $prompt,
        );

        $manager = \core\di::get(manager::class);
        $response = $manager->process_action($action);

        if (!$response->get_success()) {
            throw new \moodle_exception('aigenerationerror', 'qbank_llmjudge', '', $response->get_errormessage());
        }
        if ((int)$CFG->branch >= 500) {
            $modelused = $response->get_response_data()['model'] ?? '';
        } else {
            $modelused = $this->get_model_used($action) ?? '';
        }

        $llmoutput = $this->cleanup_response($response->get_response_data()['generatedcontent'] ?? '');
        $llmoutput = json_decode($llmoutput, true);

        if ($llmoutput === null || !isset($llmoutput['evaluations'])) {
            throw new \moodle_exception('invalidjson', 'qbank_llmjudge');
        }

        return [
         'llmoutput' => $llmoutput,
         'model' => $modelused,
        ];
    }

    /**
     * Cleans up AI response to extract pure JSON content.
     *
     * @param string $content Raw AI response.
     * @return string Clean JSON string.
     */
    private function cleanup_response(string $content): string {
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $content, $matches)) {
            $content = $matches[1];
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start !== false && $end !== false) {
            $content = substr($content, $start, $end - $start + 1);
        }
        return trim($content);
    }

    /**
     * Get the actual model used for the last AI action.
     *
     * @param \core_ai\aiactions\base $action
     * @return string|null
     */
    private function get_model_used(\core_ai\aiactions\base $action): ?string {
        global $DB;

        $params = [
            'actionname' => $action->get_basename(),
            'userid' => $action->get_configuration('userid'),
            'contextid' => $action->get_configuration('contextid'),
            'timecreated' => $action->get_configuration('timecreated'),
        ];

        $sql = "SELECT provider
                FROM {ai_action_register}
                WHERE actionname = :actionname
                AND userid = :userid
                AND timecreated = :timecreated
                AND success = 1
            ORDER BY id DESC";

        $record = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);

        $model = get_config($record->provider, 'action_generate_text_model');

        return $model;
    }
}
