<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class FeedbackFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::customerFeedbackSurvey(),
            self::survey(),
            self::poll(),
            self::evaluation(),
            self::quizAssessment(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function customerFeedbackSurvey(): array
    {
        return T::make(
            'customer-feedback',
            'Customer Feedback Survey',
            'Paged survey with NPS and open-ended comments.',
            'feedback-questionnaires',
            [
                T::page('Rating', [
                    T::field('nps', 'select', 'How likely are you to recommend us?', ['required' => true, 'column_span' => 12, 'options' => collect(range(0, 10))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()]),
                    T::field('product', 'select', 'Product', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Dynamic Fields', 'value' => 'dynamic-fields'],
                        ['label' => 'Form Builder', 'value' => 'form-builder'],
                    ]]),
                ]),
                T::page('Comments', [
                    T::field('what_worked', 'textarea', 'What worked well?', ['column_span' => 12]),
                    T::field('what_to_improve', 'textarea', 'What should we improve?', ['column_span' => 12]),
                ]),
            ],
            'pages',
            'pro',
            'heroicon-o-chat-bubble-left-right',
            'Thank you for the feedback.',
            TemplateChrome::feedbackShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function survey(): array
    {
        return T::make(
            'general-survey',
            'Survey',
            'General-purpose survey with satisfaction and demographics.',
            'feedback-questionnaires',
            [
                T::page('Survey', [
                    T::field('respondent_email', 'email', 'Email (optional)', ['column_span' => 6]),
                    T::field('satisfaction', 'radio', 'Overall satisfaction', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Very satisfied', 'value' => '5'],
                        ['label' => 'Satisfied', 'value' => '4'],
                        ['label' => 'Neutral', 'value' => '3'],
                        ['label' => 'Dissatisfied', 'value' => '2'],
                        ['label' => 'Very dissatisfied', 'value' => '1'],
                    ]]),
                    T::field('topics', 'multi_select', 'Topics covered', ['column_span' => 12, 'options' => [
                        ['label' => 'Product quality', 'value' => 'quality'],
                        ['label' => 'Support', 'value' => 'support'],
                        ['label' => 'Pricing', 'value' => 'pricing'],
                        ['label' => 'Delivery', 'value' => 'delivery'],
                    ]]),
                    T::field('comments', 'textarea', 'Additional comments', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function poll(): array
    {
        return T::make(
            'quick-poll',
            'Poll',
            'Single-question poll with optional demographics.',
            'feedback-questionnaires',
            [
                T::page('Poll', [
                    T::content('heading', ['text' => 'Quick poll']),
                    T::field('vote', 'radio', 'Your vote', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Option A', 'value' => 'a'],
                        ['label' => 'Option B', 'value' => 'b'],
                        ['label' => 'Option C', 'value' => 'c'],
                        ['label' => 'Undecided', 'value' => 'undecided'],
                    ]]),
                    T::field('age_range', 'select', 'Age range (optional)', ['column_span' => 6, 'options' => [
                        ['label' => '18–24', 'value' => '18-24'], ['label' => '25–34', 'value' => '25-34'],
                        ['label' => '35–44', 'value' => '35-44'], ['label' => '45+', 'value' => '45+'],
                    ]]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function evaluation(): array
    {
        return T::make(
            'program-evaluation',
            'Evaluation',
            'Program or session evaluation with ratings.',
            'feedback-questionnaires',
            [
                T::page('Evaluation', [
                    T::field('evaluator_name', 'text', 'Your name', ['column_span' => 6]),
                    T::field('program_name', 'text', 'Program / session', ['required' => true, 'column_span' => 6]),
                    T::field('content_rating', 'select', 'Content quality', ['required' => true, 'column_span' => 6, 'options' => collect(range(1, 5))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()]),
                    T::field('presenter_rating', 'select', 'Presenter effectiveness', ['required' => true, 'column_span' => 6, 'options' => collect(range(1, 5))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()]),
                    T::field('takeaways', 'textarea', 'Key takeaways', ['column_span' => 12]),
                    T::field('improvements', 'textarea', 'Suggestions for improvement', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function quizAssessment(): array
    {
        return T::make(
            'quiz-assessment',
            'Quiz / Assessment',
            'Multi-step knowledge check with scored questions.',
            'feedback-questionnaires',
            [
                T::page('Participant', [
                    T::field('participant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                ]),
                T::page('Questions', [
                    T::field('q1', 'radio', 'Question 1: What is 2 + 2?', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => '3', 'value' => '3'], ['label' => '4', 'value' => '4'], ['label' => '5', 'value' => '5'],
                    ]]),
                    T::field('q2', 'radio', 'Question 2: Primary color of the sky?', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Blue', 'value' => 'blue'], ['label' => 'Green', 'value' => 'green'], ['label' => 'Red', 'value' => 'red'],
                    ]]),
                    T::field('q3', 'textarea', 'Question 3: Explain your answer in one sentence', ['column_span' => 12]),
                ]),
            ],
            'wizard',
            'pro',
            'heroicon-o-clipboard-document-list',
            'Assessment submitted — results will be shared shortly.',
        );
    }
}
