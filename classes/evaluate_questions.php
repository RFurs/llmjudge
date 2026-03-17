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
 * Class responsible for defining new bulk action
 */
class evaluate_questions extends \core_question\local\bank\bulk_action_base {
    /**
     * Returns the display name for the bulk action.
     * * This text appears in the "With selected..." dropdown menu
     * at the bottom of the question bank.
     *
     * @return string The localized title of the action.
     */
    public function get_bulk_action_title(): string {
        return get_string('evaluatequestions', 'qbank_llmjudge');
    }

    /**
     * Returns the unique identifier for this bulk action.
     * * This is used for HTML element IDs and internal tracking.
     *
     * @return string The unique key for this action.
     */
    public function get_key(): string {
        return 'evaluate';
    }

    /**
     * Returns the base URL for this bulk action.
     * * This URL points to the page that will handle the processing
     * of the selected questions.
     *
     * @return moodle_url The URL to the evaluation handler.
     */
    public function get_bulk_action_url(): \moodle_url {
        return new \moodle_url('/question/bank/llmjudge/evaluate.php');
    }

    /**
     * Returns the capabilities required to use this action.
     * * Before showing this option to a user, Moodle will check
     * if they have these permissions in the current context.
     *
     * @return array List of capability names.
     */
    public function get_bulk_action_capabilities(): ?array {
        return [
            'qbank/llmjudge:evaluate',
        ];
    }
}
