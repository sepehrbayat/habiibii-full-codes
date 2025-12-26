import React from "react";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import PayoutSummary from "./PayoutSummary";
import TransactionList from "./TransactionList";

const FinanceDashboard = () => {
  return (
    <CustomStackFullWidth spacing={4} sx={{ py: 4 }}>
      <PayoutSummary />
      <TransactionList />
    </CustomStackFullWidth>
  );
};

export default FinanceDashboard;

