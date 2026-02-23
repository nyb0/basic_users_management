<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Services\FaqService;
use App\Http\Requests\FaqCreateRequest;
use App\Http\Requests\FaqUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    protected FaqService $faqService;

    /**
     * @param FaqService $faqService
     */
    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * Return paginated FAQs as JSON for the DataTable component.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function search(): JsonResponse
    {
        $searchParams = $this->getSearchParams();
        $faqs = $this->faqService->getFaqsList($searchParams);

        return response()->json($faqs);
    }

    /**
     * Get search parameters from request.
     */
    private function getSearchParams(): array
    {
        return [
            'search' => request('search'),
        ];
    }

    /**
     * Display the public FAQ page.
     */
    public function public(): Response
    {
        $faqs = $this->faqService->getAllFaqs();

        return Inertia::render('Faq', [
            'faqs' => $faqs,
            'canLogin' => route('login'),
            'canRegister' => route('register'),
        ]);
    }

    /**
     * Store a newly created FAQ in storage.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function store(FaqCreateRequest $request)
    {
        $this->faqService->createFaq($request->validated());

        return redirect()->route('site-settings.index')
            ->with('success', 'FAQ created successfully.');
    }

    /**
     * Update the specified FAQ in storage.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function update(FaqUpdateRequest $request, Faq $faq)
    {
        $this->faqService->updateFaq($faq, $request->validated());

        return redirect()->route('site-settings.index')
            ->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ from storage.
     * Note: Route access is controlled by 'role:admin' middleware.
     */
    public function destroy(Faq $faq)
    {
        $this->faqService->deleteFaq($faq);

        return redirect()->route('site-settings.index')
            ->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Display the specified FAQ.
     */
    public function show()
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit()
    {
        abort(404);
    }

    /**
     * Display a listing of the FAQs (admin).
     */
    public function index()
    {
        abort(404);
    }
}