import React from "react";
import { Typography, Box, CircularProgress, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import RetailProductCard from "./RetailProductCard";
import { useGetVendorRetailProducts } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorRetailProducts";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";
import AddIcon from "@mui/icons-material/Add";

const RetailProductList = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();

  const { data, isLoading, refetch } = useGetVendorRetailProducts(
    {
      limit: 25,
      offset: 0,
    },
    !!vendorToken
  );

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view products</Typography>
      </Box>
    );
  }

  const products = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h4" fontWeight="bold">
          Retail Products
        </Typography>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={() => router.push("/beauty/vendor/retail/products/create")}
        >
          Add Product
        </Button>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : products.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {products.map((product) => (
            <RetailProductCard key={product.id} product={product} onRefetch={refetch} />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No products found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RetailProductList;

