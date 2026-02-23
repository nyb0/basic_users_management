<template>
    <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing {{ links.current_page }} of {{ links.last_page }} pages
        </div>
        
        <nav class="flex space-x-1">
            <!-- First Page Button -->
            <Link
                :href="getFirstPageUrl()"
                :disabled="!links.first_page_url || links.current_page == 1"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    !links.first_page_url || links.current_page == 1
                        ? 'text-gray-400 cursor-not-allowed'
                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
                v-html="'&laquo;'"
            />
            
            <!-- Previous Page Button -->
            <Link
                :href="getPreviousPageUrl()"
                :disabled="!links.prev_page_url"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    links.prev_page_url
                        ? 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                        : 'text-gray-400 cursor-not-allowed'
                ]"
                v-html="'<'"
            />
            
            <!-- Page Numbers -->
            <Link
                v-for="page in visiblePages"
                :key="page"
                :href="getPageUrl(page)"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    page === links.current_page
                        ? 'bg-indigo-500 text-white'
                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
                v-text="page"
            />
            
            <!-- Next Page Button -->
            <Link
                :href="getNextPageUrl()"
                :disabled="!links.next_page_url"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    links.next_page_url
                        ? 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                        : 'text-gray-400 cursor-not-allowed'
                ]"
                v-html="'>'"
            />
            
            <!-- Last Page Button -->
            <Link
                :href="getLastPageUrl()"
                :disabled="!links.last_page_url || links.current_page == links.last_page"
                :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    !links.last_page_url || links.current_page == links.last_page
                        ? 'text-gray-400 cursor-not-allowed'
                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
                v-html="'&raquo;'"
            />
        </nav>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: Object,
});

const visiblePages = computed(() => {
    const currentPage = props.links.current_page;
    const lastPage = props.links.last_page;
    const pages = [];
    
    // Always show 5 page numbers
    const targetPages = 5;
    
    // Calculate the range to show 5 pages centered around current page
    let startPage = Math.max(1, currentPage - Math.floor(targetPages / 2));
    let endPage = Math.min(lastPage, startPage + targetPages - 1);
    
    // If we're near the end, adjust startPage to show 5 pages
    if (endPage - startPage + 1 < targetPages) {
        startPage = Math.max(1, endPage - targetPages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        // Only include pages that have valid URLs
        const pageLink = props.links.links.find(link => 
            link.label === i.toString()
        );
        if (pageLink && pageLink.url) {
            pages.push(i);
        }
    }
    
    return pages;
});

const getPageUrl = (page) => {
    // Find the link for the specific page number
    const pageLink = props.links.links.find(link => 
        link.label === page.toString()
    );
    
    if (!pageLink || !pageLink.url) {
        return '#';
    }
    
    // Parse the URL to extract the page parameter
    const url = new URL(pageLink.url, window.location.origin);
    const pageParam = url.searchParams.get('page');
    
    if (!pageParam) {
        return pageLink.url;
    }
    
    // Get current query parameters from the current URL
    const currentUrl = new URL(window.location.href);
    const currentParams = {};
    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page') {
            currentParams[key] = value;
        }
    });
    
    // Update the page parameter
    currentParams.page = page;
    
    // Build the new URL with preserved query parameters
    const newUrl = new URL(window.location.origin + window.location.pathname);
    Object.keys(currentParams).forEach(key => {
        if (currentParams[key] !== null && currentParams[key] !== undefined && currentParams[key] !== '') {
            newUrl.searchParams.set(key, currentParams[key]);
        }
    });
    
    return newUrl.toString();
};

const getFirstPageUrl = () => {
    if (!props.links.first_page_url) return '#';
    
    // Get current query parameters from the current URL
    const currentUrl = new URL(window.location.href);
    const currentParams = {};
    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page') {
            currentParams[key] = value;
        }
    });
    
    // Set page to 1
    currentParams.page = 1;
    
    // Build the new URL with preserved query parameters
    const newUrl = new URL(window.location.origin + window.location.pathname);
    Object.keys(currentParams).forEach(key => {
        if (currentParams[key] !== null && currentParams[key] !== undefined && currentParams[key] !== '') {
            newUrl.searchParams.set(key, currentParams[key]);
        }
    });
    
    return newUrl.toString();
};

const getPreviousPageUrl = () => {
    if (!props.links.prev_page_url) return '#';
    
    // Get current query parameters from the current URL
    const currentUrl = new URL(window.location.href);
    const currentParams = {};
    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page') {
            currentParams[key] = value;
        }
    });
    
    // Set page to current page - 1
    const currentPage = parseInt(currentUrl.searchParams.get('page') || props.links.current_page);
    currentParams.page = Math.max(1, currentPage - 1);
    
    // Build the new URL with preserved query parameters
    const newUrl = new URL(window.location.origin + window.location.pathname);
    Object.keys(currentParams).forEach(key => {
        if (currentParams[key] !== null && currentParams[key] !== undefined && currentParams[key] !== '') {
            newUrl.searchParams.set(key, currentParams[key]);
        }
    });
    
    return newUrl.toString();
};

const getNextPageUrl = () => {
    if (!props.links.next_page_url) return '#';
    
    // Get current query parameters from the current URL
    const currentUrl = new URL(window.location.href);
    const currentParams = {};
    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page') {
            currentParams[key] = value;
        }
    });
    
    // Set page to current page + 1
    const currentPage = parseInt(currentUrl.searchParams.get('page') || props.links.current_page);
    currentParams.page = currentPage + 1;
    
    // Build the new URL with preserved query parameters
    const newUrl = new URL(window.location.origin + window.location.pathname);
    Object.keys(currentParams).forEach(key => {
        if (currentParams[key] !== null && currentParams[key] !== undefined && currentParams[key] !== '') {
            newUrl.searchParams.set(key, currentParams[key]);
        }
    });
    
    return newUrl.toString();
};

const getLastPageUrl = () => {
    if (!props.links.last_page_url) return '#';
    
    // Get current query parameters from the current URL
    const currentUrl = new URL(window.location.href);
    const currentParams = {};
    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page') {
            currentParams[key] = value;
        }
    });
    
    // Set page to last page
    currentParams.page = props.links.last_page;
    
    // Build the new URL with preserved query parameters
    const newUrl = new URL(window.location.origin + window.location.pathname);
    Object.keys(currentParams).forEach(key => {
        if (currentParams[key] !== null && currentParams[key] !== undefined && currentParams[key] !== '') {
            newUrl.searchParams.set(key, currentParams[key]);
        }
    });
    
    return newUrl.toString();
};
</script>