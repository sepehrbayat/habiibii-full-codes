import React from "react";

const PrintableContent = React.forwardRef((props, ref) => (
  <div ref={ref} style={{ padding: "20px", backgroundColor: "#fff" }}>
    <h2>فاکتور تستی</h2>
    <p>شماره سفارش: 123456</p>
    <p>مبلغ کل: 250,000 تومان</p>
  </div>
));

PrintableContent.displayName = "PrintableContent";

export default PrintableContent;
