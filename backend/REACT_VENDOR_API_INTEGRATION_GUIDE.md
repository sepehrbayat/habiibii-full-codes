# React Vendor API Integration Guide

Complete guide for integrating Beauty Booking vendor APIs in React frontend.

## Table of Contents

1. [Quick Start](#quick-start)
2. [API Service Setup](#api-service-setup)
3. [Authentication](#authentication)
4. [Common Patterns](#common-patterns)
5. [Endpoint Examples](#endpoint-examples)
6. [Error Handling](#error-handling)
7. [Testing Guide](#testing-guide)

---

## Quick Start

### Base URL Configuration

```typescript
// config/api.ts
export const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000';
export const VENDOR_API_BASE = `${API_BASE_URL}/api/v1/beautybooking/vendor`;
```

### Authentication Setup

```typescript
// services/auth.ts
import axios from 'axios';

const getVendorToken = (): string | null => {
  return localStorage.getItem('vendor_token');
};

export const vendorApiClient = axios.create({
  baseURL: VENDOR_API_BASE,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add authentication interceptor
vendorApiClient.interceptors.request.use((config) => {
  const token = getVendorToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add error handling interceptor
vendorApiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('vendor_token');
      window.location.href = '/vendor/login';
    }
    return Promise.reject(error);
  }
);
```

---

## API Service Setup

### Base API Service Class

```typescript
// services/vendorApi.ts
import { vendorApiClient } from './auth';

export interface ApiResponse<T> {
  message: string;
  data: T;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface ApiError {
  code: string;
  message: string;
}

export interface ErrorResponse {
  errors: ApiError[];
}

class VendorApiService {
  private baseUrl = '/api/v1/beautybooking/vendor';

  // Generic GET request
  async get<T>(endpoint: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
    const response = await vendorApiClient.get(`${this.baseUrl}${endpoint}`, { params });
    return response.data;
  }

  // Generic POST request
  async post<T>(endpoint: string, data?: any, config?: any): Promise<ApiResponse<T>> {
    const response = await vendorApiClient.post(`${this.baseUrl}${endpoint}`, data, config);
    return response.data;
  }

  // Generic PUT request
  async put<T>(endpoint: string, data?: any): Promise<ApiResponse<T>> {
    const response = await vendorApiClient.put(`${this.baseUrl}${endpoint}`, data);
    return response.data;
  }

  // Generic DELETE request
  async delete<T>(endpoint: string): Promise<ApiResponse<T>> {
    const response = await vendorApiClient.delete(`${this.baseUrl}${endpoint}`);
    return response.data;
  }

  // Paginated GET request
  async getPaginated<T>(
    endpoint: string,
    params?: Record<string, any>
  ): Promise<PaginatedResponse<T>> {
    const response = await vendorApiClient.get(`${this.baseUrl}${endpoint}`, { params });
    return response.data;
  }

  // File upload
  async uploadFile<T>(
    endpoint: string,
    file: File | File[],
    fieldName: string = 'file',
    additionalData?: Record<string, any>
  ): Promise<ApiResponse<T>> {
    const formData = new FormData();
    
    if (Array.isArray(file)) {
      file.forEach((f) => {
        formData.append(`${fieldName}[]`, f);
      });
    } else {
      formData.append(fieldName, file);
    }

    if (additionalData) {
      Object.keys(additionalData).forEach((key) => {
        const value = additionalData[key];
        if (value !== null && value !== undefined) {
          if (typeof value === 'object' && !(value instanceof File)) {
            formData.append(key, JSON.stringify(value));
          } else {
            formData.append(key, value);
          }
        }
      });
    }

    const response = await vendorApiClient.post(`${this.baseUrl}${endpoint}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }
}

export const vendorApi = new VendorApiService();
```

---

## Authentication

### Login and Token Storage

```typescript
// services/auth.ts
export const vendorLogin = async (email: string, password: string) => {
  const response = await axios.post('/api/v1/vendor/auth/login', {
    email,
    password,
  });
  
  const { token } = response.data.data;
  localStorage.setItem('vendor_token', token);
  return token;
};

export const vendorLogout = () => {
  localStorage.removeItem('vendor_token');
};
```

---

## Common Patterns

### Pagination Handling

```typescript
// hooks/usePagination.ts
import { useState, useCallback } from 'react';

interface PaginationState {
  limit: number;
  offset: number;
  total: number;
  currentPage: number;
  lastPage: number;
}

export const usePagination = (initialLimit: number = 25) => {
  const [pagination, setPagination] = useState<PaginationState>({
    limit: initialLimit,
    offset: 0,
    total: 0,
    currentPage: 1,
    lastPage: 1,
  });

  const goToPage = useCallback((page: number) => {
    const offset = (page - 1) * pagination.limit;
    setPagination((prev) => ({
      ...prev,
      currentPage: page,
      offset,
    }));
  }, [pagination.limit]);

  const updatePagination = useCallback((data: PaginatedResponse<any>) => {
    setPagination((prev) => ({
      ...prev,
      total: data.total,
      currentPage: data.current_page,
      lastPage: data.last_page,
    }));
  }, []);

  return {
    pagination,
    goToPage,
    updatePagination,
    setLimit: (limit: number) => setPagination((prev) => ({ ...prev, limit, offset: 0 })),
  };
};
```

### Loading States

```typescript
// hooks/useApiCall.ts
import { useState, useCallback } from 'react';

export const useApiCall = <T, P extends any[]>(
  apiFunction: (...args: P) => Promise<T>
) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<ApiError | null>(null);
  const [data, setData] = useState<T | null>(null);

  const execute = useCallback(async (...args: P) => {
    setLoading(true);
    setError(null);
    try {
      const result = await apiFunction(...args);
      setData(result);
      return result;
    } catch (err: any) {
      const apiError: ApiError = err.response?.data?.errors?.[0] || {
        code: 'unknown',
        message: err.message || 'An error occurred',
      };
      setError(apiError);
      throw apiError;
    } finally {
      setLoading(false);
    }
  }, [apiFunction]);

  return { loading, error, data, execute };
};
```

---

## Endpoint Examples

### Booking Management

```typescript
// services/bookingService.ts
import { vendorApi } from './vendorApi';

export interface Booking {
  id: number;
  booking_reference: string;
  user: any;
  service: any;
  staff: any;
  status: string;
  booking_date: string;
  booking_time: string;
  total_amount: number;
}

export const bookingService = {
  // List bookings
  list: async (
    status: string = 'all',
    filters?: {
      date_from?: string;
      date_to?: string;
      limit?: number;
      offset?: number;
    }
  ) => {
    return vendorApi.getPaginated<Booking>(`/bookings/list/${status}`, filters);
  },

  // Get booking details
  getDetails: async (bookingId: number) => {
    return vendorApi.get<Booking>('/bookings/details', { booking_id: bookingId });
  },

  // Confirm booking
  confirm: async (bookingId: number) => {
    return vendorApi.put('/bookings/confirm', { booking_id: bookingId });
  },

  // Complete booking
  complete: async (bookingId: number) => {
    return vendorApi.put('/bookings/complete', { booking_id: bookingId });
  },

  // Mark as paid
  markPaid: async (bookingId: number) => {
    return vendorApi.put('/bookings/mark-paid', { booking_id: bookingId });
  },

  // Cancel booking
  cancel: async (bookingId: number, reason?: string) => {
    return vendorApi.put('/bookings/cancel', {
      booking_id: bookingId,
      cancellation_reason: reason,
    });
  },
};
```

### Staff Management

```typescript
// services/staffService.ts
import { vendorApi } from './vendorApi';

export interface Staff {
  id: number;
  name: string;
  email: string;
  phone: string;
  status: number;
  specializations: string[];
  working_hours: any;
}

export const staffService = {
  // List staff
  list: async (limit: number = 25, offset: number = 0) => {
    return vendorApi.getPaginated<Staff>('/staff/list', { limit, offset });
  },

  // Create staff
  create: async (data: Partial<Staff>, avatar?: File) => {
    if (avatar) {
      return vendorApi.uploadFile('/staff/create', avatar, 'avatar', data);
    }
    return vendorApi.post<Staff>('/staff/create', data);
  },

  // Update staff
  update: async (id: number, data: Partial<Staff>, avatar?: File) => {
    if (avatar) {
      return vendorApi.uploadFile(`/staff/update/${id}`, avatar, 'avatar', data);
    }
    return vendorApi.post<Staff>(`/staff/update/${id}`, data);
  },

  // Get details
  getDetails: async (id: number) => {
    return vendorApi.get<Staff>(`/staff/details/${id}`);
  },

  // Delete staff
  delete: async (id: number) => {
    return vendorApi.delete(`/staff/delete/${id}`);
  },

  // Toggle status
  toggleStatus: async (id: number) => {
    return vendorApi.get<Staff>(`/staff/status/${id}`);
  },
};
```

### Service Management

```typescript
// services/serviceService.ts
import { vendorApi } from './vendorApi';

export interface Service {
  id: number;
  name: string;
  description: string;
  duration_minutes: number;
  price: number;
  status: number;
  category: any;
  staff: any[];
}

export const serviceService = {
  // List services
  list: async (limit: number = 25, offset: number = 0) => {
    return vendorApi.getPaginated<Service>('/service/list', { limit, offset });
  },

  // Create service
  create: async (data: Partial<Service>, image?: File) => {
    if (image) {
      return vendorApi.uploadFile('/service/create', image, 'image', data);
    }
    return vendorApi.post<Service>('/service/create', data);
  },

  // Update service
  update: async (id: number, data: Partial<Service>, image?: File) => {
    if (image) {
      return vendorApi.uploadFile(`/service/update/${id}`, image, 'image', data);
    }
    return vendorApi.post<Service>(`/service/update/${id}`, data);
  },

  // Get details
  getDetails: async (id: number) => {
    return vendorApi.get<Service>(`/service/details/${id}`);
  },

  // Delete service
  delete: async (id: number) => {
    return vendorApi.delete(`/service/delete/${id}`);
  },

  // Toggle status
  toggleStatus: async (id: number) => {
    return vendorApi.get<Service>(`/service/status/${id}`);
  },
};
```

### Calendar Management

```typescript
// services/calendarService.ts
import { vendorApi } from './vendorApi';

export interface CalendarBlock {
  id: number;
  date: string;
  start_time: string;
  end_time: string;
  type: 'break' | 'holiday' | 'manual_block';
  reason?: string;
}

export const calendarService = {
  // Get availability
  getAvailability: async (date: string, staffId?: number, serviceId?: number) => {
    return vendorApi.get<{
      date: string;
      available_slots: string[];
      duration_minutes: number;
    }>('/calendar/availability', { date, staff_id: staffId, service_id: serviceId });
  },

  // Create block
  createBlock: async (data: {
    date: string;
    start_time: string;
    end_time: string;
    type: 'break' | 'holiday' | 'manual_block';
    reason?: string;
    staff_id?: number;
  }) => {
    return vendorApi.post<CalendarBlock>('/calendar/blocks/create', data);
  },

  // Delete block
  deleteBlock: async (id: number) => {
    return vendorApi.delete(`/calendar/blocks/delete/${id}`);
  },
};
```

### Salon Management

```typescript
// services/salonService.ts
import { vendorApi } from './vendorApi';

export const salonService = {
  // Register salon
  register: async (data: {
    business_type: string;
    license_number: string;
    license_expiry: string;
    working_hours: Array<{ day: string; open: string; close: string }>;
  }) => {
    return vendorApi.post('/salon/register', data);
  },

  // Upload documents
  uploadDocuments: async (files: File[]) => {
    return vendorApi.uploadFile('/salon/documents/upload', files, 'documents');
  },

  // Update working hours
  updateWorkingHours: async (workingHours: Array<{ day: string; open: string; close: string }>) => {
    return vendorApi.post('/salon/working-hours/update', { working_hours: workingHours });
  },

  // Manage holidays
  manageHolidays: async (action: 'add' | 'remove' | 'replace', holidays: string[]) => {
    return vendorApi.post('/salon/holidays/manage', { action, holidays });
  },

  // Get profile
  getProfile: async () => {
    return vendorApi.get('/profile');
  },

  // Update profile
  updateProfile: async (data: any) => {
    return vendorApi.post('/profile/update', data);
  },
};
```

---

## Error Handling

### Error Handler Utility

```typescript
// utils/errorHandler.ts
import { ApiError } from '../services/vendorApi';

export const handleApiError = (error: any): string => {
  if (error.response?.data?.errors) {
    const apiError: ApiError = error.response.data.errors[0];
    return apiError.message;
  }
  return error.message || 'An error occurred';
};

// Usage in components
try {
  await bookingService.confirm(bookingId);
} catch (error) {
  const errorMessage = handleApiError(error);
  toast.error(errorMessage);
}
```

### Error Boundary Component

```typescript
// components/ErrorBoundary.tsx
import React, { Component, ErrorInfo, ReactNode } from 'react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
  public state: State = {
    hasError: false,
    error: null,
  };

  public static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('Uncaught error:', error, errorInfo);
  }

  public render() {
    if (this.state.hasError) {
      return (
        <div>
          <h2>Something went wrong.</h2>
          <details>
            {this.state.error && this.state.error.toString()}
          </details>
        </div>
      );
    }

    return this.props.children;
  }
}
```

---

## Testing Guide

### Mock Data Examples

```typescript
// __mocks__/bookingMock.ts
export const mockBooking: Booking = {
  id: 100001,
  booking_reference: 'BB-100001',
  user: {
    id: 1,
    f_name: 'John',
    l_name: 'Doe',
  },
  service: {
    id: 1,
    name: 'Haircut',
  },
  staff: {
    id: 1,
    name: 'Jane Smith',
  },
  status: 'confirmed',
  booking_date: '2024-01-20',
  booking_time: '10:00',
  total_amount: 100000,
};

export const mockBookingsResponse: PaginatedResponse<Booking> = {
  message: 'Data retrieved successfully',
  data: [mockBooking],
  total: 1,
  per_page: 25,
  current_page: 1,
  last_page: 1,
};
```

### API Mocking with MSW

```typescript
// mocks/handlers.ts
import { rest } from 'msw';

export const handlers = [
  rest.get('/api/v1/beautybooking/vendor/bookings/list/:status', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json({
        message: 'Data retrieved successfully',
        data: [mockBooking],
        total: 1,
        per_page: 25,
        current_page: 1,
        last_page: 1,
      })
    );
  }),

  rest.put('/api/v1/beautybooking/vendor/bookings/confirm', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json({
        message: 'Booking confirmed successfully',
        data: { ...mockBooking, status: 'confirmed' },
      })
    );
  }),
];
```

### Test Scenarios

```typescript
// __tests__/bookingService.test.ts
import { bookingService } from '../services/bookingService';
import { mockBookingsResponse } from '../__mocks__/bookingMock';

describe('BookingService', () => {
  it('should list bookings', async () => {
    const result = await bookingService.list('all');
    expect(result.data).toBeDefined();
    expect(result.total).toBeGreaterThan(0);
  });

  it('should confirm booking', async () => {
    const result = await bookingService.confirm(100001);
    expect(result.data.status).toBe('confirmed');
  });

  it('should handle errors', async () => {
    try {
      await bookingService.confirm(999999);
    } catch (error) {
      expect(error.code).toBe('not_found');
    }
  });
});
```

---

## Best Practices

1. **Always handle errors:** Use try-catch blocks and error boundaries
2. **Show loading states:** Use loading indicators during API calls
3. **Validate data:** Validate input before sending to API
4. **Cache responses:** Use React Query or SWR for caching
5. **Handle pagination:** Use pagination hooks for list endpoints
6. **File uploads:** Use FormData for file uploads
7. **Type safety:** Use TypeScript interfaces for all API responses
8. **Error messages:** Display user-friendly error messages

---

**Last Updated:** 2025-12-03  
**Version:** 1.0

