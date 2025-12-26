import React, { useEffect, useState } from "react";
import { useRouter } from "next/router";
import { getVendorToken } from "../../contexts/vendor-auth-context";

const VendorAuthGuard = (props) => {
  const { children, from } = props;
  const router = useRouter();
  const [checked, setChecked] = useState(false);

  useEffect(
    () => {
      if (!router.isReady) {
        return;
      }
      const vendorToken = getVendorToken();
      if (vendorToken) {
        setChecked(true);
      } else {
        router.push(
          {
            pathname: "/beauty/vendor/login",
            query: { from: from },
          },
          undefined,
          { shallow: true }
        );
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [router.isReady]
  );

  if (!checked) {
    return null;
  }

  // If got here, it means that the redirect did not occur, and that tells us that the vendor is
  // authenticated / authorized.

  return <>{children}</>;
};

export default VendorAuthGuard;

