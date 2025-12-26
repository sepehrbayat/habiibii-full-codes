import { useMediaQuery, useTheme } from "@mui/material";
import { useRouter } from "next/router";
import PropTypes from "prop-types";
import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import useGetModule from "../../api-manage/hooks/react-query/useGetModule";
import { setSelectedModule } from "../../redux/slices/utils";
import { CustomStackFullWidth } from "../../styled-components/CustomStyles.style";
import FooterComponent from "../footer";
import HeaderComponent from "../header";
import BottomNav from "../header/BottomNav";
import { MainLayoutRoot } from "./LandingLayout";
import useGetLandingPage from "api-manage/hooks/react-query/useGetLandingPage";
import { ModuleTypes } from "../../helper-functions/moduleTypes";

const MainLayout = ({ children, configData }) => {
	const [rerenderUi, setRerenderUi] = useState(false);
	const { data, refetch } = useGetModule();
	const theme = useTheme();
	const isSmall = useMediaQuery("(max-width:1180px)");
	const router = useRouter();
	const { page } = router.query;
	const dispatch = useDispatch();
	useEffect(() => {
		if (router.pathname === "/home") {
			refetch();
		}
	}, []);

	useEffect(() => {
		if (!data || data.length === 0) return;
		const storedModule =
			typeof window !== "undefined"
				? JSON.parse(localStorage.getItem("module"))
				: null;
		const currentType = storedModule?.module_type;
		const isBeautyRoute = router.pathname.startsWith("/beauty");
		const desiredType = isBeautyRoute ? ModuleTypes.BEAUTY : currentType;

		const findModule = (type) =>
			data.find((item) => item?.module_type === type);

		let moduleToPersist = null;
		if (desiredType) {
			moduleToPersist = findModule(desiredType);
		}
		if (!moduleToPersist && currentType) {
			moduleToPersist = findModule(currentType);
		}
		if (!moduleToPersist) {
			moduleToPersist = data[0];
		}

		if (moduleToPersist) {
			localStorage.setItem("module", JSON.stringify(moduleToPersist));
			dispatch(setSelectedModule(moduleToPersist));

			const zoneIds = moduleToPersist?.zones
				?.map((zone) => zone?.id)
				.filter(Boolean);
			const currentZoneIds = (() => {
				try {
					return JSON.parse(localStorage.getItem("zoneid"));
				} catch (e) {
					return null;
				}
			})();
			if (zoneIds?.length && !Array.isArray(currentZoneIds)) {
				localStorage.setItem("zoneid", JSON.stringify(zoneIds));
			}
		}
	}, [data, dispatch, router.pathname]);

	const { landingPageData } = useSelector((state) => state.configData);
	const { data: landing, refetch: landingRefetch } = useGetLandingPage();
	useEffect(() => {
		if (!landingPageData) {
			landingRefetch();
		}
	}, []);

	return (
		<MainLayoutRoot justifyContent="space-between" key={rerenderUi}>
			<header>
				<HeaderComponent />
			</header>
			<CustomStackFullWidth mt={isSmall ? "3.5rem" : "5.9rem"}>
				<CustomStackFullWidth sx={{ minHeight: "70vh" }}>
					{children}
				</CustomStackFullWidth>
			</CustomStackFullWidth>
			<footer>
				<FooterComponent
					configData={configData}
					landingPageData={landingPageData ?? landing}
				/>
			</footer>
			{isSmall && page !== "parcel" && <BottomNav />}
		</MainLayoutRoot>
	);
};

MainLayout.propTypes = {
	children: PropTypes.node,
};

export default React.memo(MainLayout);
