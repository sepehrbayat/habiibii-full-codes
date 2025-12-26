import { useContext } from 'react';
import { VendorAuthContext } from './vendor-auth-context';

export const useVendorAuth = () => useContext(VendorAuthContext);

