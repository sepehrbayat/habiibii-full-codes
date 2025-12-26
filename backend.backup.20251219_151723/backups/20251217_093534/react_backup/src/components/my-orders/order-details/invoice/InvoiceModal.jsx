import React from "react";
import {
  Dialog,
  DialogContent,
  DialogActions,
  Box,
  Typography,
  Divider,
  Table,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  Button,
  CircularProgress,
} from "@mui/material";
import logo from "../../../../../public/static/logo.png";

const InvoiceModal = ({ open, handleClose, order, t }) => {
  if (!order) {
    return (
      <Dialog open={open} onClose={handleClose} maxWidth="md" fullWidth>
        <DialogContent>
          <Box display="flex" justifyContent="center" alignItems="center" minHeight={200}>
            <CircularProgress />
          </Box>
        </DialogContent>
      </Dialog>
    );
  }
  
  const products = order?.items || [];
  const itemPrice = products.reduce((sum, product) => {
    const price = product.price || 0;
    const quantity = product.quantity || 1;
    return sum + price * quantity;
  }, 0);
  const discount = order?.store_discount_amount || 0;
  const serviceFee = order?.additional_charge || 0;
  const deliveryFee = order?.delivery_charge || 0;
  const subtotal = itemPrice;
  const total = itemPrice - discount + serviceFee + deliveryFee;
  

  const handlePrint = () => {
    const printSection = document.getElementById("print-section");
    if (!printSection) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
        <head>
          <title>${t("Invoice")}</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
          </style>
        </head>
        <body>
          ${printSection.innerHTML}
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 3,
      maximumFractionDigits: 3
    }).format(value);
  };

  const renderInvoiceContent = () => (
    <Box sx={{ backgroundColor: "#fff", padding: 3, width: "100%" }}>
      <Box sx={{ textAlign: "center", mb: 3 }}>
        <img src={logo.src} alt="Logo" style={{ maxHeight: 60 }} />
        <Typography variant="h6" mt={1}>{t("Invoice")}</Typography>
      </Box>

      <Box mb={2}>
        <Typography><strong>{t("Order ID")}:</strong> {order.id}</Typography>
        <Typography><strong>{t("Status")}:</strong> {order.order_status}</Typography>
        <Typography><strong>{t("Payment Method")}:</strong> {order.payment_method?.replaceAll("_", " ")}</Typography>
        <Typography><strong>{t("Address")}:</strong> {order.delivery_address?.address}</Typography>
      </Box>

      <Divider sx={{ my: 2 }} />

      {products.length > 0 ? (
        <Table sx={{ mb: 2 }}>
          <TableHead>
            <TableRow>
              <TableCell>{t("Item")}</TableCell>
              <TableCell align="center">{t("Unit")}</TableCell>
              <TableCell align="right">{t("Unit Price")}</TableCell>
              <TableCell align="center">{t("Qty")}</TableCell>
              <TableCell align="right">{t("Total")}</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {products.map((product, index) => (
              <TableRow key={index}>
                <TableCell>{product.name || "test"}</TableCell>
                <TableCell align="center">{product.unit || "Kg"}</TableCell>
                <TableCell align="right">{formatCurrency(product.price || 1)}</TableCell>
                <TableCell align="center">{product.quantity || 1}</TableCell>
                <TableCell align="right">{formatCurrency((product.price || 1) * (product.quantity || 1))}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      ) : (
        <Typography>{t("No products to display")}</Typography>
      )}

      <Divider sx={{ my: 2 }} />

      <Box sx={{ textAlign: "right" }}>
        <Typography><strong>{t("Items Price")}:</strong> {formatCurrency(itemPrice)}</Typography>
        <Typography><strong>{t("Discount")}:</strong> -{formatCurrency(discount)}</Typography>
        <Typography><strong>{t("Subtotal")}:</strong> {formatCurrency(subtotal)}</Typography>
        <Typography><strong>{t("Service Fee")}:</strong> {formatCurrency(serviceFee)}</Typography>
        <Typography><strong>{t("Delivery Fee")}:</strong> {formatCurrency(deliveryFee)}</Typography>
        <Typography fontWeight={700}><strong>{t("Total")}:</strong> {formatCurrency(total)}</Typography>
      </Box>
    </Box>
  );

  return (
    <>
      {/* محتوای قابل پرینت */}
      <div id="print-section" style={{ display: "none" }}>
        {renderInvoiceContent()}
      </div>

      {/* مودال نمایشی */}
      <Dialog 
        open={open} 
        onClose={handleClose} 
        maxWidth="md" 
        fullWidth
        aria-labelledby="invoice-dialog-title"
      >
        <DialogContent>
          {renderInvoiceContent()}
        </DialogContent>
        <DialogActions>
          <Button variant="outlined" onClick={handleClose}>{t("Close")}</Button>
          <Button variant="contained" onClick={handlePrint}>{t("Print")}</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default InvoiceModal;