import React, { useState } from "react";
import { Box, TextField, Button, Typography, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { toast } from "react-hot-toast";
import { useCreateRetailProduct } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCreateRetailProduct";

const RetailProductForm = ({ onSuccess }) => {
  const router = useRouter();
  const { mutate: createProduct, isLoading } = useCreateRetailProduct();
  const [formData, setFormData] = useState({
    name: "",
    description: "",
    price: "",
    stock_quantity: "",
    category: "",
    image: null,
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e) => {
    setFormData((prev) => ({ ...prev, image: e.target.files[0] }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    createProduct(formData, {
      onSuccess: (res) => {
        toast.success(res?.message || "Product created successfully");
        if (onSuccess) onSuccess();
        else router.push("/beauty/vendor/retail/products");
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to create product");
      },
    });
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Add New Product
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={3}>
          <TextField
            label="Name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Description"
            name="description"
            value={formData.description}
            onChange={handleChange}
            multiline
            rows={4}
            fullWidth
          />

          <TextField
            label="Price"
            name="price"
            type="number"
            value={formData.price}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Stock Quantity"
            name="stock_quantity"
            type="number"
            value={formData.stock_quantity}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Category"
            name="category"
            value={formData.category}
            onChange={handleChange}
            fullWidth
          />

          <Box>
            <Typography variant="body2" gutterBottom>
              Product Image
            </Typography>
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
            />
          </Box>

          <Box display="flex" gap={2}>
            <Button
              type="submit"
              variant="contained"
              color="primary"
              disabled={isLoading}
            >
              {isLoading ? "Creating..." : "Create Product"}
            </Button>
            <Button
              variant="outlined"
              onClick={() => router.back()}
            >
              Cancel
            </Button>
          </Box>
        </CustomStackFullWidth>
      </Box>
    </CustomStackFullWidth>
  );
};

export default RetailProductForm;

