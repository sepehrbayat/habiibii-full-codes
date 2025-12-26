import usFlag from "./assets/us.svg";
import arabicFlag from "./assets/arabic-flag-svg.svg";
import spain from "./assets/spain.png";
import bangladesh from "./assets/bangladesh.png";
import irFlag from "./assets/iran.svg";
export const languageList = [
  {
    languageName: "English",
    languageCode: "en",
    countryCode: "US",
    countryFlag: usFlag.src,
  },
  // {
  //   languageName: "Spanish",
  //   languageCode: "es",
  //   countryCode: "es",
  //   countryFlag: spain.src,
  // },
  {
    languageName: "Arabic",
    languageCode: "ar",
    countryCode: "SA",
    countryFlag: arabicFlag.src,
  },
  // {
  //   languageName: "Bengali",
  //   languageCode: "bn",
  //   countryCode: "BN",
  //   countryFlag: bangladesh.src,
  // },
  {
    languageName: "Persian",
    languageCode: "fa",
    countryCode: "IR",
    countryFlag: irFlag.src,
  },
];
