import React from "react";
import { Card, CardContent, Typography, Box } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const RetailProductCard = ({ product, onRefetch }) => {
  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {product.name || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {product.description || "No description"}
              </Typography>
            </Box>
          </Box>

          <Box>
            <Typography variant="body2" color="text.secondary">
              Price: ${product.price || 0}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Stock: {product.stock_quantity || 0}
            </Typography>
            {product.category && (
              <Typography variant="body2" color="text.secondary">
                Category: {product.category}
              </Typography>
            )}
          </Box>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default RetailProductCard;

