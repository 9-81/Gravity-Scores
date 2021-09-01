<?php
/**
 * SCORES
 * Adds user-scores to evaluables and subscales. Each test
 * given as $data needs to be annotated with a `entry_id`
 * attribute to use ist.
 *
 * Provides the `gravityscores_score_evaluable` filter,
 * which is used to add additional support for new
 * question types, see: `hooks/scores/*.php`
 */

namespace gravityscores;

class Score
{

    /**
     * Extends exported data.
     * Tests need to be annotated with an `entry_id`
     */
    public static function export($data)
    {
        if (! array_key_exists('tests', $data)) {
            return $data;
        }

        $scored = self::score_evaluables($data);
       
        foreach ($scored['tests'] as $test_index => $test) {
            foreach ($test['subscales'] as $subscale_index => $subscale) {
                $score = array_reduce($subscale['evaluables'], function ($score, $evaluable) {
                    return $score + ($evaluable['score'] ?? 0);
                }, 0);

                $scored['tests'][$test_index]['subscales'][$subscale_index]['score'] = $score;
            }
        }

        return $scored;
    }

    private static function score_evaluables($data)
    {
        if (! array_key_exists('tests', $data)) {
            return $data;
        }

        foreach ($data['tests'] as $test_index => $test) {
            $form = \GFAPI::get_form($test['form_id']);

            $entry_id = $test['entry_id'] ?? false;

            // Set all scores to zero if there is no entry for the user
            if ($entry_id == false) {
                foreach ($test['subscales'] as $subscale_index => $subscale) {
                    foreach ($subscale['evaluables'] as $evaluable_index => $evaluable) {
                        $data['tests'][$test_index]['subscales'][$subscale_index]['evaluables'][$evaluable_index]['score'] = 0;
                    }
                }
                return $data;
            }

            // Calculate and append scores
            foreach ($test['subscales'] as $subscale_index => $subscale) {
                foreach ($subscale['evaluables'] as $evaluable_index => $evaluable) {
                    $field = current(array_filter($form['fields'], function ($field) use ($evaluable) {
                        return $evaluable['field_id'] == $field['id'];
                    }));

                    $filtered_chunk = \apply_filters('gravityscores_score_evaluable', [
                        'evaluable' => $evaluable,
                        'field' => $field,
                        'entry' => \GFAPI::get_entry($entry_id)
                    ]);

                    $data['tests'][$test_index]['subscales'][$subscale_index]['evaluables'][$evaluable_index] = $filtered_chunk['evaluable'];
                    unset($data['tests'][$test_index]['entry_id']);
                }
            }
        }

        return $data;
    }
}
