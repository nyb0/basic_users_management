<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FaqService
{
    /**
     * Get paginated list of FAQs.
     *
     * @param array $searchParams
     * @return LengthAwarePaginator
     */
    public function getFaqsList(array $searchParams = []): LengthAwarePaginator
    {
        $search = $searchParams['search'] ?? null;

        return Faq::query()
            ->search($search)
            ->orderByPriority()
            ->paginate(10);
    }

    /**
     * Get all FAQs ordered by priority (for public display).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFaqs()
    {
        return Faq::query()
            ->orderByPriority()
            ->get();
    }

    /**
     * Create a new FAQ.
     *
     * @param array $data
     * @return Faq
     */
    public function createFaq(array $data): Faq
    {
        return Faq::create([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'priority' => $data['priority'] ?? 0,
        ]);
    }

    /**
     * Update an existing FAQ.
     *
     * @param Faq $faq
     * @param array $data
     * @return Faq
     */
    public function updateFaq(Faq $faq, array $data): Faq
    {
        $faq->update([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'priority' => $data['priority'] ?? $faq->priority,
        ]);

        return $faq;
    }

    /**
     * Delete a FAQ.
     *
     * @param Faq $faq
     * @return void
     */
    public function deleteFaq(Faq $faq): void
    {
        $faq->delete();
    }
}