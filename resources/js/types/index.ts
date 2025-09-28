export interface Content {
  id: number;
  provider_id: string;
  provider_name: string;
  title: string;
  type: 'video' | 'article';
  tags: string[];
  views?: number;
  likes?: number;
  duration?: string;
  reading_time?: number;
  reactions?: number;
  comments?: number;
  published_at: string;
  score: number | null;
  created_at: string;
  updated_at: string;
}

export interface SearchFilters {
  query?: string;
  type?: 'video' | 'article';
  sort?: 'relevance' | 'date' | 'popularity';
  order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export interface SearchResponse {
  success: boolean;
  data: Content[];
  pagination: PaginationData;
  filters: SearchFilters;
}

export interface ContentStats {
  total_contents: number;
  total_videos: number;
  total_articles: number;
  avg_score: number | null;
  last_updated: string | null;
}

export interface PageProps {
  [key: string]: any;
}
