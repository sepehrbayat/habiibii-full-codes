import axios from "axios";
export const baseUrl = process.env.NEXT_PUBLIC_BASE_URL;
const MainApi = axios.create({
  baseURL: baseUrl,
});
MainApi.interceptors.request.use(function (config) {
  let zoneid = undefined;
  let token = undefined;
  let vendorToken = undefined;
  let language = undefined;
  let currentLocation = undefined;
  let software_id = 33571750;
  let hostname = process.env.NEXT_CLIENT_HOST_URL;
  let moduleid = undefined;

  if (typeof window !== "undefined") {
    zoneid = localStorage.getItem("zoneid");
    token = localStorage.getItem("token");
    vendorToken = localStorage.getItem("vendor_token");
    language = JSON.parse(localStorage.getItem("language-setting"));
    currentLocation = JSON.parse(localStorage.getItem("currentLatLng"));
    moduleid = JSON.parse(localStorage.getItem("module"))?.id;
  }
  
  // Check if this is a vendor API route
  const isVendorRoute = config.url && config.url.includes("/beautybooking/vendor/");
  
  if (currentLocation) config.headers.latitude = currentLocation.lat;
  if (currentLocation) config.headers.longitude = currentLocation.lng;
  if (zoneid) {
    let zoneIdValue = zoneid;
    try {
      JSON.parse(zoneid);
      zoneIdValue = zoneid;
    } catch (e) {
      if (zoneid.includes(",")) {
        const zones = zoneid.split(",").map(z => parseInt(z.trim())).filter(z => !isNaN(z));
        zoneIdValue = JSON.stringify(zones);
      } else {
        const zoneNum = parseInt(zoneid);
        if (!isNaN(zoneNum)) {
          zoneIdValue = JSON.stringify([zoneNum]);
        }
      }
    }
    config.headers.zoneId = zoneIdValue;
  }
  if (moduleid) config.headers.moduleId = moduleid;
  
  // Use vendor token for vendor routes only; never fall back to customer token
  if (isVendorRoute) {
    if (vendorToken) {
      config.headers.authorization = `Bearer ${vendorToken}`;
    } else {
      delete config.headers.authorization;
    }
  } else if (token) {
    config.headers.authorization = `Bearer ${token}`;
  } else {
    delete config.headers.authorization;
  }
  
  if (language) config.headers["X-localization"] = language;
  if (hostname) config.headers["origin"] = hostname;
  config.headers["X-software-id"] = software_id;
  config.headers["Accept"] = 'application/json'
  config.headers["ngrok-skip-browser-warning"] = true;
  return config;
});
// MainApi.interceptors.response.use(
//     (response) => response,
//     (error) => {
//         if (error.response.status === 401) {
//             toast.error(t('Your token has been expired.please sign in again'), {
//                 id: 'error',
//             })
//             localStorage.removeItem('token')
//             store.dispatch(removeToken())
//         }
//         return Promise.reject(error)
//     }
// )

export default MainApi;
