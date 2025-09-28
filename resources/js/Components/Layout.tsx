/// <reference types="vite/client" />
import { Head } from '@inertiajs/react';

interface LayoutProps {
  title?: string;
  children: React.ReactNode;
}

export default function Layout({ title = 'Search Engine', children }: LayoutProps) {
  return (
    <>
      <Head title={title} />
      <div className="min-h-screen bg-gray-50">
        <nav className="bg-white shadow-sm border-b">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex justify-between items-center h-16">
            </div>
          </div>
        </nav>
        
        <main className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          {children}
        </main>
        
        <footer className="bg-white border-t mt-12">
          <div className="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div className="text-center text-sm text-gray-500">
              © 2025 Search API - Built with Laravel & React
            </div>
          </div>
        </footer>
      </div>
    </>
  );
}
