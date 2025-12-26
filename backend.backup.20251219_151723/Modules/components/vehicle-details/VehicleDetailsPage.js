import { Grid } from "@mui/material";
import CustomContainer from "components/container";
import VehicleDetailsReview from "./VehicleDetailsReview";
import VisitVendor from "./VisitVendor";
import VehicleDetailsTopSection from "./VehicleDetailsTopSection";
import VehicleFromThisVendor from "./VehicleFromThisVendor";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { useGetVehicleDetails } from "../../rental-api-manage/hooks/react-query/details/useGetVehicleDetails";
import { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { Stack } from "@mui/system";
import useScrollToTop from "api-manage/hooks/custom-hooks/useScrollToTop";
import useGetVehicleReview from "components/home/module-wise-components/rental/rental-api-manage/hooks/react-query/details/useGetVehicleReview";

const VehicleDetailsPage = () => {
	useScrollToTop();
	const router = useRouter();
	const { id, from } = router.query;
	const { cartList } = useSelector((state) => state.cart);
	const rentalSearch = useSelector(
		(state) => state?.rentalSearch?.rentalSearch
	);
	const [selectedPricing, setSelectedPricing] = useState("hourly");
	const [typeWisePrice, setTypeWisePrice] = useState(null);
	const { data: vehicleDetails } = useGetVehicleDetails(id);

	const handleSelect = (type) => {
		if (!rentalSearch?.tripType) {
			setSelectedPricing(type);
			if (type === "distance_wise") {
				setTypeWisePrice(vehicleDetails?.distance_price);
			} else {
				setTypeWisePrice(vehicleDetails?.hourly_price);
			}
		}
	};
	
	const isProductExist = () => {
		return cartList?.carts?.find(
			(item) => item.vehicle?.id === vehicleDetails?.id
		);
	};
	useEffect(() => {
		if (vehicleDetails) {

			if (isProductExist()) {
				if(cartList?.user_data?.rental_type==="hourly"){
				  setTypeWisePrice(vehicleDetails?.hourly_price)
				}else{
				  setTypeWisePrice(vehicleDetails?.distance_price)
				}
				setSelectedPricing(cartList?.user_data?.rental_type
				)
			  } else if (rentalSearch) {
				  setSelectedPricing(rentalSearch?.tripType)
			  }else(
				setSelectedPricing(null)
			  )
		}
	}, [rentalSearch?.tripType, cartList, from, vehicleDetails]);


	if (!vehicleDetails) {
		return null;
	}
	return (
		<CustomContainer>
			<CustomStackFullWidth>
				<Grid container spacing={2.5} sx={{ mt: {xs:"10px",md:"24px"} }}>
					<Grid item xs={12} lg={9}>
						<Stack sx={{ position: "sticky", top: "100px" }}>
							<VehicleDetailsTopSection
								vehicleDetails={vehicleDetails}
								handleSelect={handleSelect}
								selectedPricing={selectedPricing}
								tripHours={rentalSearch?.duration}
								typeWisePrice={typeWisePrice}
								userData={
									cartList?.carts?.length > 0 &&
									cartList?.user_data
								}
								from={from}
							/>
							<VehicleDetailsReview
								vehicleDetails={vehicleDetails}
								borderRadius="10px"
							/>
						</Stack>
					</Grid>
					<Grid item xs={12} lg={3}>
						<Stack sx={{ position: "sticky", top: "100px" }}>
							<VisitVendor vehicleDetails={vehicleDetails} />
							<VehicleFromThisVendor
								vehicleDetails={vehicleDetails}
							/>
						</Stack>
					</Grid>
				</Grid>
				{/*<VehicleDetailsRentThisCar vehicleDetails={vehicleDetails} />*/}
			</CustomStackFullWidth>
		</CustomContainer>
	);
};

export default VehicleDetailsPage;
