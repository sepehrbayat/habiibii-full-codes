import { LandingLayout } from "components/layout/LandingLayout";
import LandingPage from "../src/components/landing-page";
import CssBaseline from "@mui/material/CssBaseline";
import React, { useEffect } from "react";
import { useDispatch } from "react-redux";
import { setConfigData, setLandingPageData } from "redux/slices/configData";
import Router from "next/router";
import SEO from "../src/components/seo";
import useGetLandingPage from "../src/api-manage/hooks/react-query/useGetLandingPage";
import { useGetConfigData } from "../src/api-manage/hooks/useGetConfigData";
import axios from "axios";
// import DynamicFavicon from "components/DynamicFavicon";

const Root = (props) => {
	const { configData, landingPageData, language } = props;
	const { data, refetch } = useGetLandingPage();
	const dispatch = useDispatch();
	const { data: dataConfig, refetch: configRefetch } = useGetConfigData();

	useEffect(() => {
		configRefetch();
		refetch();
	}, [refetch]);

	useEffect(() => {
		if (language && !localStorage.getItem("language-setting")) {
			localStorage.setItem(
				"language-setting",
				JSON.stringify(language)
			);

			if (language === "en") {
				window.localStorage.setItem("settings", JSON.stringify({ direction: "ltr" }));
			} else {
				window.localStorage.setItem("settings", JSON.stringify({ direction: "rtl" }));
			}
		}
	}, [language]);

	useEffect(() => {
		dispatch(setLandingPageData(data));
		if (dataConfig) {
			if (dataConfig.length === 0) {
				Router.push("/404");
			} else if (dataConfig?.maintenance_mode) {
				Router.push("/maintainance");
			} else {
				dispatch(setConfigData(dataConfig));
			}
		}
	}, [dataConfig, data]);

	return (
		<>
			<CssBaseline />
			{/* <DynamicFavicon configData={configData} /> */}
			<SEO
				image={landingPageData?.meta_image||configData?.fav_icon_full_url}
				businessName={configData?.business_name}
				configData={configData}
				title={landingPageData?.meta_title||configData?.business_name}
				description={landingPageData?.meta_description||configData?.meta_description}
			/>
			{data && (
				<LandingLayout configData={dataConfig} landingPageData={data}>
					<LandingPage
						configData={dataConfig}
						landingPageData={data}
					/>
				</LandingLayout>
			)}
		</>
	);
};
export default Root;


export const getServerSideProps = async ({ req, res }) => {
	let language = null;
	let ip = null;
	let countryCode = null;

	language = req.cookies.languageSetting || null;

	if (!language) {
		try {
			ip = req.headers["x-forwarded-for"]?.split(",")[0] || req.socket?.remoteAddress;

			if (ip === "::1" || ip === "127.0.0.1") {
				language = "en";
			} else {
				countryCode = "US";

				const { data: geoData } = await axios.get(`http://ip-api.com/json/${ip}`);
				countryCode = geoData?.countryCode || null;

				if (countryCode === "IR") language = "fa";
				else if (
					["AE", "SA", "EG", "IQ", "SY", "OM", "KW", "QA", "BH", "JO", "LB", "YE", "DZ", "MA", "TN", "SD"].includes(
						countryCode
					)
				) {
					language = "ar";
				} else {
					language = "en";
				}

			}
		} catch (error) {
			console.error(" خطا در تشخیص موقعیت جغرافیایی (geo lookup):", error);
		}
	}


	if (!language) {
		language = "en";
	}

	res.setHeader("Set-Cookie", `languageSetting=${language}; Path=/; HttpOnly`);

	let config = null;
	let landingPageData = null;
	try {
		({ data: config } = await axios.get(`${process.env.NEXT_PUBLIC_BASE_URL}/api/v1/config`, {
			headers: {
				"X-software-id": 33571750,
				"X-server": "server",
				"X-localization": language,
				origin: process.env.NEXT_CLIENT_HOST_URL,
			}
		}));

		({ data: landingPageData } = await axios.get(`${process.env.NEXT_PUBLIC_BASE_URL}/api/v1/react-landing-page`, {
			headers: {
				"X-software-id": 33571750,
				"X-server": "server",
				"X-localization": language,
				origin: process.env.NEXT_CLIENT_HOST_URL,
			}
		}));
	} catch (err) {
		console.error(" خطا در دریافت config:", err);
	}

	// Set cache control headers for 1 hour (3600 seconds)
	res.setHeader(
		"Cache-Control",
		"public, s-maxage=3600, stale-while-revalidate"
	);

	return {
		props: {
			configData: config,
			landingPageData: landingPageData,
			language
		},
	};
};
