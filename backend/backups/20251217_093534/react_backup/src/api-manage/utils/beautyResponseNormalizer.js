/**
 * Normalize beauty API list responses to a consistent shape.
 * Handles Laravel paginated responses and plain arrays.
 */
export const normalizeBeautyResponse = (response, params = {}) => {
  const payload = response || {};
  const rawItems = payload.data ?? payload;
  const items = Array.isArray(rawItems) ? rawItems : rawItems?.data ?? [];

  const perPage =
    payload.per_page ??
    payload.pagination?.per_page ??
    params?.limit ??
    params?.per_page ??
    (items.length || 0);

  const total =
    payload.total ??
    payload.pagination?.total ??
    (typeof payload.count === "number" ? payload.count : items.length);

  const currentPage =
    payload.current_page ??
    payload.pagination?.current_page ??
    (params?.offset && perPage
      ? Math.floor(params.offset / perPage) + 1
      : 1);

  const lastPage =
    payload.last_page ??
    payload.pagination?.last_page ??
    (perPage ? Math.max(1, Math.ceil((total || items.length) / perPage)) : 1);

  return {
    data: items,
    total,
    per_page: perPage,
    current_page: currentPage,
    last_page: lastPage,
    message: payload.message,
  };
};

/**
 * Normalize beauty API single item responses.
 */
export const normalizeBeautyItemResponse = (response) => {
  const payload = response || {};
  return {
    data: payload.data ?? payload,
    message: payload.message,
  };
};

