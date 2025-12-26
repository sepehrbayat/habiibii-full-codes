import React from "react";
import { Card, CardContent, Typography, Box, Button, Chip, CardMedia } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { getImageUrl } from "utils/CustomFunctions";

const RetailProductCard = ({ product, onAddToCart, configData }) => {
  const isOutOfStock = product.stock_quantity === 0 || product.stock_quantity < 1;

  const handleAddToCart = () => {
    if (isOutOfStock) return;
    if (onAddToCart) {
      onAddToCart(product);
    }
  };

  const productImage = product.image
    ? getImageUrl({ value: product.image }, "product_image_url", configData)
    : "/static/no-image-found.png";

  return (
    <Card sx={{ height: "100%", display: "flex", flexDirection: "column" }}>
      {productImage && (
        <CardMedia
          component="img"
          height="200"
          image={productImage}
          alt={product.name}
          sx={{ objectFit: "cover" }}
        />
      )}
      <CardContent sx={{ flexGrow: 1 }}>
        <CustomStackFullWidth spacing={2}>
          <Box>
            <Typography variant="h6" fontWeight="bold">
              {product.name}
            </Typography>
            {product.description && (
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                {product.description}
              </Typography>
            )}
          </Box>

          <Box display="flex" justifyContent="space-between" alignItems="center">
            <Typography variant="h5" color="primary" fontWeight="bold">
              ${product.price || 0}
            </Typography>
            {product.stock_quantity !== undefined && (
              <Chip
                label={isOutOfStock ? "Out of Stock" : `${product.stock_quantity} available`}
                color={isOutOfStock ? "error" : "success"}
                size="small"
              />
            )}
          </Box>

          {product.category && (
            <Typography variant="body2" color="text.secondary">
              Category: {product.category.name || product.category}
            </Typography>
          )}

          <Button
            variant="contained"
            color="primary"
            onClick={handleAddToCart}
            fullWidth
            disabled={isOutOfStock}
          >
            {isOutOfStock ? "Out of Stock" : "Add to Cart"}
          </Button>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default RetailProductCard;

