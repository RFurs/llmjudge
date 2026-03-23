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

namespace qbank_llmjudge\event;

use core\event\question_moved;

/**
 * Event observer for question movements
 *
 */
class question_moved_observer {
    /**
     * Set new contextid for the moved question.
     *
     * @param question_moved $event
     * @return void
     */
    public static function delete_question_evaluation(question_moved $event): void {
        global $DB;
        $DB->set_field(
            'qbank_llmjudge_eval',
            'contextid',
            $event->contextid,
            [
                'questionid' => $event->objectid,
            ],
        );
    }
}
