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
 * Class responsible for converting JSON to Moodle XML
 */
class questions_encoder {
    /**
     * Extracts questions and encodes them as JSON
     *
     * @param array $questionids Comma separated string or array of question IDs.
     * @param \context $context The context where questions are being accessed.
     * @return string JSON encoded question data.
     */
    public function encode_questions_to_json($questionids, $context) {
        global $DB;

        if (empty($questionids)) {
            return json_encode([]);
        }
        if (!is_array($questionids)) {
            $questionids = explode(',', $questionids ?? '');
        }

        $selectedquestionsdata = [];

        [$insql, $params] = $DB->get_in_or_equal($questionids);
        $questions = $DB->get_records_select('question', "id $insql", $params);

        foreach ($questions as $q) {
            $qtypeobj = \question_bank::get_qtype($q->qtype);
            $qtypeobj->get_question_options($q);

            $answers = [];
            if (isset($q->options->answers)) {
                foreach ($q->options->answers as $answer) {
                    $answers[] = [
                        'text' => html_to_text($answer->answer),
                        'is_correct' => ($answer->fraction > 0),
                        'weight' => (float)$answer->fraction,
                        'feedback' => html_to_text($answer->feedback),
                    ];
                }
            }
            $selectedquestionsdata[] = [
                'question_id' => (int)$q->id,
                'type' => $q->qtype,
                'name' => $q->name,
                'question_text' => strip_tags(format_text($q->questiontext, $q->questiontextformat, ['context' => $context->id])),
                'options' => $answers,
            ];
        }

        return json_encode($selectedquestionsdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
