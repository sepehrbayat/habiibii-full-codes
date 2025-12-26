// Colors

const neutral = {
	90: "#FFFFFF1A",
	100: "#FFFFFF",
	200: "#E5E7EB",
	300: "#F6F7FB",
	400: "#9CA3AF",
	500: "#6B7280",
	600: "#4B5563",
	700: "#374151",
	800: "#1F2937",
	900: "#111827",
	1000: "#212E28",
	1100: "#D6D6D6",
};

const background = {
	default: "#F9FAFC",
	paper: "#FFFFFF",
	custom: "#f1f2f5",
	custom2: "#FFFFFF",
	custom3: "#F6F7FB",
	custom4: "#ffffff",
	footer1: "#9f9f9f1a",
	footer2: "#9f9f9f0d",
	custom5: "#F4F6F8",
	custom6: "#FCFCFD",
	custom7: "#F6F6F6",
};

const divider = "#E6E8F0";

const primary = {
	main: "#F60",
	deep: "#D35400",
	light: "rgba(255, 102, 0, 0.2)",
	dark: "#C04800",
	semiLight: "rgba(255, 102, 0, 0.1)",
	contrastText: "#FFFFFF",
	customType1: "#F36A1F",
	customType2: "#F67828",
	customType3: "#FF8800",
	overLay: "rgba(255, 102, 0, 0.4)",
	lite: "rgba(255, 102, 0, 0.1)",
	icon: "#F60",
};
const moduleTheme = {
	pharmacy: "#F60",
	ecommerce: "#F60",
	food: "#F60",
	parcel: "#F60",
};
const horizontalCardBG = "rgba(255, 102, 0, 0.1)";

const secondary = {
	main: "#FA7D2E",
	light: "rgba(255, 120, 50, 0.2)",
	dark: "#D06020",
	contrastText: "#FFFFFF",
};

const success = {
	main: "#F78D2E",
	light: "rgba(255, 120, 50, 0.2)",
	dark: "#D06020",
	contrastText: "#FFFFFF",
};

const info = {
	main: "#FF9D4D",
	light: "rgba(255, 157, 77, 0.3)",
	dark: "#D97C00",
	lite: "rgba(255, 157, 77, 0.1)",
	contrastText: "#FFFFFF",
	contrastText1: "#F5F6F8",
	blue: "#FF9D4D",
	custom1: "#FF8C3F",
};

const warning = {
	main: "#F97316",
	light: "#FFD6A0",
	lite: "#FFD6A0",
	liter: "rgba(255, 170, 85, 0.2)",
	dark: "#D66A13",
	contrastText: "#FFFFFF",
	new: "#FFA726",
};

const error = {
	main: "#FF6D60",
	light: "#FFA07A",
	dark: "#D14343",
	contrastText: "#FFFFFF",
	deepLight: "#FF725E",
};

const text = {
	primary: "#360f00", // #642903 #F76000
	secondary: "#32201a", // #9f6654 #FFA385
	disabled: "rgba(255, 120, 50, 0.5)",
	custom: "#ad4a29", // #F56C3F
	customText1: "#8b3a24", // #F86941
};

const footer = {
	inputButton: "#FFD6A0",
	inputButtonHover: "#FFB071",
	bottom: "rgba(255, 120, 50, 0.3)",
	foodBottom: "#FFA07A",
	appDownloadButtonBg: "#1A1A1A",
	appDownloadButtonBgGray: "#1A1A1A", // #F76A40
	appDownloadButtonBgHover: "#FF8345",
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

const foodCardColor = "#FFF6EF";
const paperBoxShadow = "#E5EAF1";
const roundStackOne = "rgba(255, 102, 0, 0.04)";
const roundStackTwo = "rgba(255, 102, 0, 0.06)";
const toolTipColor = neutral[1000];

export const lightThemeOptions = {
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
						backgroundColor: neutral[200],
						"& .MuiChip-deleteIcon": {
							color: neutral[400],
						},
					},
					"&.MuiChip-outlinedDefault": {
						"& .MuiChip-deleteIcon": {
							color: neutral[300],
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
						"-webkit-box-shadow": "0 0 0 100px #f0f5f5 inset",
						WebkitTextFillColor: "#000",
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
					color: neutral[500],
				},
				track: {
					backgroundColor: neutral[400],
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
					backgroundColor: neutral[100],
					".MuiTableCell-root": {
						color: neutral[700],
					},
				},
			},
		},
	},
	palette: {
		action: {
			active: neutral[500],
			focus: "rgba(55, 65, 81, 0.12)",
			hover: "rgba(55, 65, 81, 0.04)",
			selected: "rgba(55, 65, 81, 0.08)",
			disabledBackground: "rgba(55, 65, 81, 0.12)",
			disabled: "rgba(55, 65, 81, 0.26)",
		},
		horizontalCardBG,
		background,
		divider,
		error,
		info,
		mode: "light",
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
		"0px 1px 1px rgba(100, 116, 139, 0.06), 0px 1px 2px rgba(100, 116, 139, 0.1)",
		"0px 1px 2px rgba(100, 116, 139, 0.12)",
		"0px 1px 4px rgba(100, 116, 139, 0.12)",
		"0px 1px 5px rgba(100, 116, 139, 0.12)",
		"0px 1px 6px rgba(100, 116, 139, 0.12)",
		"0px 2px 6px rgba(100, 116, 139, 0.12)",
		"0px 3px 6px rgba(100, 116, 139, 0.12)",
		"0px 2px 4px rgba(31, 41, 55, 0.06), 0px 4px 6px rgba(100, 116, 139, 0.12)",
		"0px 5px 12px rgba(100, 116, 139, 0.12)",
		"0px 5px 14px rgba(100, 116, 139, 0.12)",
		"0px 5px 15px rgba(100, 116, 139, 0.12)",
		"0px 6px 15px rgba(100, 116, 139, 0.12)",
		"0px 7px 15px rgba(100, 116, 139, 0.12)",
		"0px 8px 15px rgba(100, 116, 139, 0.12)",
		"0px 9px 15px rgba(100, 116, 139, 0.12)",
		"0px 10px 15px rgba(100, 116, 139, 0.12)",
		"0px 12px 22px -8px rgba(100, 116, 139, 0.25)",
		"0px 13px 22px -8px rgba(100, 116, 139, 0.25)",
		"0px 14px 24px -8px rgba(100, 116, 139, 0.25)",
		"0px 10px 10px rgba(31, 41, 55, 0.04), 0px 20px 25px rgba(31, 41, 55, 0.1)",
		"0px 25px 50px rgba(100, 116, 139, 0.25)",
		"0px 25px 50px rgba(100, 116, 139, 0.25)",
		"0px 25px 50px rgba(100, 116, 139, 0.25)",
		"0px 25px 50px rgba(100, 116, 139, 0.25)",
	],
};
