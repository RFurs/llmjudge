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
        global $USER;

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

        return $this->cleanup_response($response->get_response_data()['generatedcontent'] ?? '');
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
}
