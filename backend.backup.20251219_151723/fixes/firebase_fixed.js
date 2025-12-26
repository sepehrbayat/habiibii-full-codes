import { initializeApp, getApps, getApp } from "firebase/app";
import {
  getMessaging,
  getToken,
  onMessage,
  isSupported,
} from "firebase/messaging";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
  apiKey: "AIzaSyD0Z911mOoWCVkeGdjhIKwWFPRgvd6ZyAw",
  authDomain: "stackmart-500c7.firebaseapp.com",
  projectId: "stackmart-500c7",
  storageBucket: "stackmart-500c7.appspot.com",
  messagingSenderId: "491987943015",
  appId: "1:491987943015:web:d8bc7ab8dbc9991c8f1ec2",
  measurementId: "",
};

// Initialize Firebase app with error handling
// راه‌اندازی Firebase app با مدیریت خطا
let firebaseApp;
try {
  firebaseApp = !getApps().length
    ? initializeApp(firebaseConfig)
    : getApp();
} catch (error) {
  console.error("Error initializing Firebase app:", error);
  firebaseApp = null;
}

// Initialize messaging with error handling
// راه‌اندازی messaging با مدیریت خطا
const messaging = (async () => {
  try {
    if (!firebaseApp) {
      return null;
    }
    const isSupportedBrowser = await isSupported();
    if (isSupportedBrowser) {
      return getMessaging(firebaseApp);
    }
    return null;
  } catch (err) {
    console.warn("Firebase messaging not supported or error:", err);
    return null;
  }
})();

/**
 * Fetch FCM token with error handling
 * دریافت FCM token با مدیریت خطا
 * 
 * @param {Function} setTokenFound Callback to set token found status
 * @param {Function} setFcmToken Callback to set FCM token
 */
export const fetchToken = async (setTokenFound, setFcmToken) => {
  try {
    const messagingInstance = await messaging;
    
    // Check if messaging is available
    // بررسی اینکه آیا messaging در دسترس است
    if (!messagingInstance) {
      console.warn("Firebase messaging is not available");
      setTokenFound(false);
      setFcmToken(null);
      return;
    }

    return getToken(messagingInstance, {
      vapidKey:
        "BIYqKZ2ZlXRJYZX_iU7oYymqHZ1B0d8MVsYMoEPX_eFtezlxZ_V4JooCxS8ks857ylCVLewTtgHFxc6I8iBi7h4",
    })
      .then((currentToken) => {
        if (currentToken) {
          setTokenFound(true);
          setFcmToken(currentToken);
          // Track the token -> client mapping, by sending to backend server
          // show on the UI that permission is secured
        } else {
          setTokenFound(false);
          setFcmToken(null);
          // shows on the UI that permission is required
        }
      })
      .catch((err) => {
        // Handle specific error codes
        // مدیریت کدهای خطای خاص
        if (err.code === "messaging/permission-blocked") {
          console.warn("Notification permission was blocked by user");
        } else if (err.code === "messaging/permission-default") {
          console.warn("Notification permission is in default state");
        } else {
          console.warn("Error getting FCM token:", err);
        }
        setTokenFound(false);
        setFcmToken(null);
      });
  } catch (error) {
    console.error("Error in fetchToken:", error);
    setTokenFound(false);
    setFcmToken(null);
  }
};

/**
 * Set up message listener with error handling
 * راه‌اندازی message listener با مدیریت خطا
 * 
 * @returns {Promise} Promise that resolves with message payload
 */
export const onMessageListener = async () => {
  return new Promise((resolve, reject) => {
    (async () => {
      try {
        const messagingResolve = await messaging;
        
        // Check if messaging is available
        // بررسی اینکه آیا messaging در دسترس است
        if (!messagingResolve) {
          console.warn("Firebase messaging is not available for onMessage");
          reject(new Error("Messaging not available"));
          return;
        }

        // Set up message handler with null check
        // راه‌اندازی message handler با بررسی null
        try {
          onMessage(messagingResolve, (payload) => {
            resolve(payload);
          });
        } catch (error) {
          // Handle case where onMessage fails (e.g., messaging not initialized)
          // مدیریت حالتی که onMessage ناموفق است (مثلاً messaging راه‌اندازی نشده)
          console.warn("Error setting up onMessage listener:", error);
          reject(error);
        }
      } catch (error) {
        console.error("Error in onMessageListener:", error);
        reject(error);
      }
    })();
  });
};

// Export auth with null check
// Export کردن auth با بررسی null
export const auth = firebaseApp ? getAuth(firebaseApp) : null;

