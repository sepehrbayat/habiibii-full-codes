import React, { useMemo, useState } from "react";
import { Typography, Box, CircularProgress, FormControl, InputLabel, Select, MenuItem, Grid, Pagination } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import RetailProductCard from "./RetailProductCard";
import useGetRetailProducts from "../../../../../api-manage/hooks/react-query/beauty/useGetRetailProducts";
import useGetCategories from "../../../../../api-manage/hooks/react-query/beauty/useGetCategories";
import { useRouter } from "next/router";
import { useSelector } from "react-redux";

const RetailProducts = () => {
  const router = useRouter();
  const { salon_id } = router.query;
  const { configData } = useSelector((state) => state.configData);
  const [categoryFilter, setCategoryFilter] = useState("");
  const [offset, setOffset] = useState(0);
  const limit = 25;

  const { data, isLoading, refetch } = useGetRetailProducts(
    {
      salon_id: salon_id || undefined,
      limit,
      offset,
      category_id: categoryFilter || undefined,
    },
    true
  );

  const { data: categoriesData } = useGetCategories();
  const categories = categoriesData?.data || [];

  const products = data?.data || [];
  const totalPages = useMemo(() => {
    if (!data?.total) return 1;
    return Math.max(1, Math.ceil(data.total / limit));
  }, [data?.total, limit]);

  const handleAddToCart = (product) => {
    // This will be handled by a cart context or Redux store
    // For now, we'll store in localStorage
    const cart = JSON.parse(localStorage.getItem("beauty_retail_cart") || "[]");
    const existingItem = cart.find((item) => item.product_id === product.id);
    
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.push({
        product_id: product.id,
        product_name: product.name,
        price: product.price,
        quantity: 1,
        image: product.image,
      });
    }
    
    localStorage.setItem("beauty_retail_cart", JSON.stringify(cart));
    // You can add a toast notification here
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center" flexWrap="wrap" gap={2}>
        <Typography variant="h4" fontWeight="bold">
          Retail Products
        </Typography>
        <FormControl sx={{ minWidth: 200 }}>
          <InputLabel>Filter by Category</InputLabel>
          <Select
            value={categoryFilter}
            label="Filter by Category"
            onChange={(e) => {
              setCategoryFilter(e.target.value);
              setOffset(0);
            }}
          >
            <MenuItem value="">All Categories</MenuItem>
            {categories.map((category) => (
              <MenuItem key={category.id} value={category.id}>
                {category.name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : products.length > 0 ? (
        <Grid container spacing={3}>
          {products.map((product) => (
            <Grid item xs={12} sm={6} md={4} lg={3} key={product.id}>
              <RetailProductCard
                product={product}
                onAddToCart={handleAddToCart}
                configData={configData}
              />
            </Grid>
          ))}
        </Grid>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No products found
          </Typography>
        </Box>
      )}

      {totalPages > 1 && (
        <Box display="flex" justifyContent="center">
          <Pagination
            count={totalPages}
            page={Math.floor(offset / limit) + 1}
            onChange={(event, value) => setOffset((value - 1) * limit)}
            color="primary"
          />
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RetailProducts;

