// Colors

const neutral = {
  100: "#000000",
  200: "#303032",
  300: "#111827",
  400: "#9CA3AF",
  500: "#C5C5CA",
  600: "#F0F0FA",
  700: "#F0F2F4",
  800: "#1F2937",
  900: "#111827",
  1000: "#FFFFFF",
  1100: "#D6D6D6",
};

const moduleTheme = {
  pharmacy: "#FF6C3F",
  ecommerce: "#FFA385",
  food: "#FF5722",
  parcel: "#FF9D4D",
};

const background = {
  default: "#0B0F19",
  paper: neutral[900],
  custom: "#282829",
  custom2: "#1F2937",
  custom3: neutral[800],
  custom4: "#000000",
  footer1: "rgba(255, 102, 0, 0.1)",
  footer2: "rgba(255, 102, 0, 0.1)",
  custom5: "#282829",
  custom6: "rgba(255, 102, 0, 0.05)",
  custom7: "#000000",
};

const horizontalCardBG = neutral[900];
const divider = "#FF8C3F";
const foodCardColor = neutral[800];
const roundStackOne = "rgba(255, 102, 0, 0.04)";
const roundStackTwo = "rgba(255, 102, 0, 0.06)";

const primary = {
  main: "#F60",
  light: "rgba(255, 102, 0, 0.2)",
  dark: "#C04800",
  deep: "#B23700",
  contrastText: neutral[900],
  semiLight: "rgba(255, 102, 0, 0.1)",
  overLay: "#000000",
  customType2: "#FF6C3F",
  lite: "rgba(255, 102, 0, 0.1)",
  customType3: "#FF5722",
  icon: "#F60",
};

const secondary = {
  main: "#FA7D2E",
  light: "rgba(255, 120, 50, 0.2)",
  dark: "#D0661F",
  contrastText: neutral[900],
};

const success = {
  main: "#FF8C3F",
  light: "rgba(255, 140, 63, 0.3)",
  dark: "#D8652E",
  contrastText: neutral[900],
};

const info = {
  main: "#FF9D4D",
  light: "rgba(255, 157, 77, 0.3)",
  dark: "#D97800",
  lite: "rgba(255, 157, 77, 0.1)",
  contrastText: neutral[900],
  contrastText1: "#F5F6F8",
  blue: "#FF9D4D",
  custom1: "#FF8C3F",
};

const warning = {
  main: "#F97316",
  light: "#FFD6A0",
  dark: "#D66A13",
  lite: "#FFD6A0",
  liter: "rgba(255, 170, 85, 0.2)",
  contrastText: neutral[900],
  new: "#FFA726",
};

const error = {
  main: "#FF6D60",
  light: "#FFA07A",
  dark: "#D14343",
  contrastText: neutral[900],
  deepLight: "#FF725E",
};

const text = {
  primary: "#e8eaec",
  secondary: "#FFB071",
  disabled: "rgba(255, 157, 77, 0.48)",
  custom: "#FF9D4D",
  customText1: "#F5A27F",
};

const footer = {
  inputButton: "rgba(255, 157, 77, 0.2)",
  inputButtonHover: "#FF8C3F",
  bottom: "rgba(255, 120, 50, 0.3)",
  foodBottom: "#FFA385",
  appDownloadButtonBg: "#1A1A1A",
  appDownloadButtonBgGray: "#FF9D4D",
  foodFooterBg: "#FF975E",
};

const customColor = {
  textGray: "#FFB071",
  textGrayDeep: "#FFA85C",
  buyButton: "#FF7E50",
  parcelWallet: "#FF6F3F",
};

const whiteContainer = {
  main: "#ffffff",
};

const pink = {
  main: "#FF6D76",
};

const toolTipColor = "#FF8C3F";
const paperBoxShadow = "#E5EAF1";
export const darkThemeOptions = {
  components: {
    MuiAvatar: {
      styleOverrides: {
        root: {
          backgroundColor: neutral[500],
          color: "#FFFFFF",
        },
      },
    },
    MuiChip: {
      styleOverrides: {
        root: {
          "&.MuiChip-filledDefault": {
            backgroundColor: neutral[800],
            "& .MuiChip-deleteIcon": {
              color: neutral[500],
            },
          },
          "&.MuiChip-outlinedDefault": {
            borderColor: neutral[700],
            "& .MuiChip-deleteIcon": {
              color: neutral[700],
            },
          },
        },
      },
    },
    MuiInputBase: {
      styleOverrides: {
        input: {
          "&::placeholder": {
            opacity: 1,
            color: text.secondary,
          },
        },
      },
    },
    MuiOutlinedInput: {
      styleOverrides: {
        notchedOutline: {
          borderColor: divider,
        },
        input: {
          "&:-webkit-autofill": {
            "-webkit-box-shadow": "0 0 0 100px #282929 inset",
            WebkitTextFillColor: "#fff",
          },
        },
      },
    },
    MuiMenu: {
      styleOverrides: {
        paper: {
          borderColor: divider,
          borderStyle: "solid",
          borderWidth: 1,
        },
      },
    },
    MuiPopover: {
      styleOverrides: {
        paper: {
          borderColor: divider,
          borderStyle: "solid",
          borderWidth: 1,
        },
      },
    },
    MuiSwitch: {
      styleOverrides: {
        switchBase: {
          color: neutral[100],
        },
        track: {
          backgroundColor: neutral[500],
          opacity: 1,
        },
      },
    },
    MuiTableCell: {
      styleOverrides: {
        root: {
          borderBottom: `1px solid ${divider}`,
        },
      },
    },
    MuiTableHead: {
      styleOverrides: {
        root: {
          backgroundColor: neutral[800],
          ".MuiTableCell-root": {
            color: neutral[300],
          },
        },
      },
    },
  },
  palette: {
    action: {
      active: neutral[400],
      hover: "rgba(255, 255, 255, 0.04)",
      selected: "rgba(255, 255, 255, 0.08)",
      disabledBackground: "rgba(255, 255, 255, 0.12)",
      disabled: "rgba(255, 255, 255, 0.26)",
    },
    horizontalCardBG,
    background,
    divider,
    error,
    info,
    mode: "dark",
    neutral,
    primary,
    secondary,
    success,
    text,
    warning,
    footer,
    customColor,
    whiteContainer,
    pink,
    paperBoxShadow,
    foodCardColor,
    moduleTheme,
    roundStackOne,
    roundStackTwo,
    toolTipColor,
  },
  shadows: [
    "none",
    "0px 1px 2px rgba(0, 0, 0, 0.24)",
    "0px 1px 2px rgba(0, 0, 0, 0.24)",
    "0px 1px 4px rgba(0, 0, 0, 0.24)",
    "0px 1px 5px rgba(0, 0, 0, 0.24)",
    "0px 1px 6px rgba(0, 0, 0, 0.24)",
    "0px 2px 6px rgba(0, 0, 0, 0.24)",
    "0px 3px 6px rgba(0, 0, 0, 0.24)",
    "0px 4px 6px rgba(0, 0, 0, 0.24)",
    "0px 5px 12px rgba(0, 0, 0, 0.24)",
    "0px 5px 14px rgba(0, 0, 0, 0.24)",
    "0px 5px 15px rgba(0, 0, 0, 0.24)",
    "0px 6px 15px rgba(0, 0, 0, 0.24)",
    "0px 7px 15px rgba(0, 0, 0, 0.24)",
    "0px 8px 15px rgba(255, 255, 255, 0.07)",
    "0px 9px 15px rgba(0, 0, 0, 0.24)",
    "0px 10px 15px rgba(0, 0, 0, 0.24)",
    "0px 12px 22px -8px rgba(0, 0, 0, 0.24)",
    "0px 13px 22px -8px rgba(0, 0, 0, 0.24)",
    "0px 14px 24px -8px rgba(0, 0, 0, 0.24)",
    "0px 20px 25px rgba(0, 0, 0, 0.24)",
    "0px 25px 50px rgba(0, 0, 0, 0.24)",
    "0px 25px 50px rgba(0, 0, 0, 0.24)",
    "0px 25px 50px rgba(0, 0, 0, 0.24)",
    "0px 25px 50px rgba(0, 0, 0, 0.24)",
  ],
};
