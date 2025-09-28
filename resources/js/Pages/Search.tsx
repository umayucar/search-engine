/// <reference types="vite/client" />
import { useState } from 'react';
import { router } from '@inertiajs/react';
import Layout from '../Components/Layout';
import SearchForm from '../Components/SearchForm';
import ContentCard from '../Components/ContentCard';
import Pagination from '../Components/Pagination';
import { Content, SearchFilters, PaginationData, ContentStats, PageProps } from '../types';

interface SearchPageProps extends PageProps {
  contents: Content[];
  pagination: PaginationData;
  filters: SearchFilters;
  stats: ContentStats;
}

export default function Search({ contents, pagination, filters, stats }: SearchPageProps) {
  const [loading, setLoading] = useState(false);
  const [syncLoading, setSyncLoading] = useState(false);

  const handleSearch = (newFilters: SearchFilters) => {
    setLoading(true);
    
    // Clean up empty filters
    const cleanFilters = Object.entries(newFilters).reduce((acc, [key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        acc[key] = value;
      }
      return acc;
    }, {} as any);

    router.get('/search', cleanFilters, {
      preserveState: true,
      onFinish: () => setLoading(false),
    });
  };

  const handlePageChange = (page: number) => {
    handleSearch({ ...filters, page });
  };

  const handleSync = async () => {
    setSyncLoading(true);
    try {
      const response = await fetch('/api/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      
      if (response.ok) {
        // Refresh the page to show new data
        router.reload();
      }
    } catch (error) {
      console.error('Sync failed:', error);
    } finally {
      setSyncLoading(false);
    }
  };

  return (
    <Layout title="Search Content">
      <div className="space-y-6">
        <div className="bg-white rounded-lg shadow-sm border p-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-2xl font-bold text-gray-900 mb-2">
                Content Search Engine
              </h1>
              <div className="flex flex-wrap gap-4 text-sm text-gray-600">
                <span>📊 {stats.total_contents} total items</span>
                <span>🎥 {stats.total_videos} videos</span>
                <span>📝 {stats.total_articles} articles</span>
              </div>
            </div>
            <div className="mt-4 sm:mt-0">
              <button
                onClick={handleSync}
                disabled={syncLoading}
                className="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {syncLoading ? (
                  <>
                    <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Syncing...
                  </>
                ) : (
                  <>
                    <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Data
                  </>
                )}
              </button>
            </div>
          </div>
        </div>

        <SearchForm 
          filters={filters} 
          onSearch={handleSearch} 
          loading={loading} 
        />

        {contents.length > 0 ? (
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">
                Search Results ({pagination.total} items found)
              </h2>
              <div className="text-sm text-gray-500">
                Page {pagination.current_page} of {pagination.last_page}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {contents.map((content) => (
                <ContentCard key={content.id} content={content} />
              ))}
            </div>

            <Pagination 
              pagination={pagination} 
              onPageChange={handlePageChange} 
            />
          </div>
        ) : (
          <div className="bg-white rounded-lg shadow-sm border p-12 text-center">
            <div className="text-6xl mb-4">🔍</div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {filters.query ? 'No results found' : 'Start searching'}
            </h3>
            <p className="text-gray-600 mb-4">
              {filters.query 
                ? `No content matches your search criteria. Try adjusting your filters.`
                : 'Use the search form above to find content from our providers.'
              }
            </p>
            {!stats.total_contents && (
              <p className="text-sm text-gray-500">
                💡 Tip: Click "Sync Data" to fetch content from providers first.
              </p>
            )}
          </div>
        )}
      </div>
    </Layout>
  );
}
