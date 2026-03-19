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
 * Class responsible for building a prompt to LLM
 */
class prompt_builder {
    /**
     * Function building a prompt using data received from form.
     * @param \stdClass $data The form data (cognitive_difficulty)
     * @param string $questions The question that have to be evaluated
     * @return string The fully constructed prompt
     */
    public function build(\stdClass $data, string $questions): string {
        global $CFG;
        $a = new \stdClass();
        $a->cognitive_difficulty = $data->cognitive_difficulty;

        $currentlang = \current_language();
        $lang = (substr($currentlang, 0, 2) === 'lt') ? 'lt' : 'en';
        $bloomdifficulty = ($lang === 'lt')
            ? "Numatytas kognityvinis lygis: {$a->cognitive_difficulty}"
            : "Intended cognitive level: {$a->cognitive_difficulty}";

        $prompt = $this->get_prompt();
        $prompt = $prompt . "\n{$questions}";
        $prompt = $prompt . "\n\n{$bloomdifficulty}";

        return $prompt;
    }

    /**
     * Private function that extracts the prompt from the file at llmjudge\data
     * @return string The fully constructed prompt
     */
    private function get_prompt() {
        global $CFG;

        $currentlang = \current_language();
        $lang = (substr($currentlang, 0, 2) === 'lt') ? 'lt' : 'en';

        $path = __DIR__ . "/../data/{$lang}/prompt.txt";

        if (file_exists($path)) {
            return file_get_contents($path);
        }
    }
}
