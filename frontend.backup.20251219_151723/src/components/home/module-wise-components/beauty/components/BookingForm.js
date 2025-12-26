import React, { useState, useEffect } from "react";
import {
  Typography,
  Box,
  TextField,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Card,
  CardContent,
} from "@mui/material";
import Stepper from "@mui/material/Stepper";
import Step from "@mui/material/Step";
import StepLabel from "@mui/material/StepLabel";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { useCreateBooking } from "../../../../../api-manage/hooks/react-query/beauty/useCreateBooking";
import useGetSalonDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetSalonDetails";
import useGetSalons from "../../../../../api-manage/hooks/react-query/beauty/useGetSalons";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import dayjs from "dayjs";
import AvailabilityCalendar from "./AvailabilityCalendar";
import ServiceSuggestions from "./ServiceSuggestions";
import { useSelector } from "react-redux";

const BookingForm = () => {
  const router = useRouter();
  const { configData } = useSelector((state) => state.configData);
  const { salon_id, service_id } = router.query;
  const { mutate: createBooking, isLoading } = useCreateBooking();
  const [activeStep, setActiveStep] = useState(0);
  const [selectedDate, setSelectedDate] = useState(null);
  const [formData, setFormData] = useState({
    salon_id: salon_id || "",
    service_id: service_id || "",
    staff_id: "",
    booking_date: null,
    booking_time: "",
    payment_method: "cash_payment",
    payment_gateway: "",
    notes: "",
  });
  const [salonParams, setSalonParams] = useState({ limit: 50 });
  const selectedSalonId = formData?.salon_id || salon_id;
  const { data: salonsData, isLoading: salonsLoading } = useGetSalons(salonParams, true);
  const { data: salonData } = useGetSalonDetails(
    selectedSalonId,
    !!selectedSalonId
  );

  const salons = salonsData?.data || salonsData?.salons || [];
  const salon = salonData?.data || salonData;
  const services = salon?.services || [];
  const staff = salon?.staff || [];

  useEffect(() => {
    if (salon_id) {
      setFormData((prev) => ({ ...prev, salon_id }));
    }
    if (service_id) {
      setFormData((prev) => ({ ...prev, service_id }));
    }
  }, [salon_id, service_id]);

  const convertPaymentMethod = (value) =>
    value === "online" ? "digital_payment" : value;

  const digitalGateways =
    configData?.active_payment_method_list?.filter(
      (method) => method?.type === "digital_payment"
    ) || [];

  const handleStepValidation = (step) => {
    const hasValidDate =
      selectedDate && dayjs(selectedDate).isValid();
    const hasTime = !!formData.booking_time;
    if (step === 0 && !formData.salon_id) {
      toast.error("Please select a salon");
      return false;
    }
    if (step === 1 && !formData.service_id) {
      toast.error("Please select a service");
      return false;
    }
    if (step === 2) {
      if (!hasValidDate) {
        toast.error("Please choose a date");
        return false;
      }
      if (!hasTime) {
        toast.error("Please choose a time slot");
        return false;
      }
    }
    return true;
  };

  const isValidTime = (time) => /^\d{2}:\d{2}$/.test(time);

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!formData.salon_id || !formData.service_id || !formData.booking_date || !formData.booking_time) {
      toast.error("Please fill in all required fields");
      return;
    }

    if (!isValidTime(formData.booking_time)) {
      toast.error("Please select a valid booking time (HH:MM)");
      return;
    }

    const paymentMethod = convertPaymentMethod(formData.payment_method);

    if (paymentMethod === "digital_payment" && !formData.payment_gateway) {
      toast.error("Please select a payment gateway");
      return;
    }

    const callbackUrl =
      typeof window !== "undefined"
        ? `${window.location.origin}/beauty/bookings`
        : undefined;

    createBooking(
      {
        ...formData,
        booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
        booking_time: formData.booking_time,
        payment_method: paymentMethod,
        payment_gateway: paymentMethod === "digital_payment" ? formData.payment_gateway : undefined,
        callback_url: callbackUrl,
      },
      {
        onSuccess: (response) => {
          toast.success("Booking created successfully");
          if (response?.data?.redirect_url) {
            window.location.href = response.data.redirect_url;
          } else {
            router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
          }
        },
        onError: (error) => {
          // Handle 401 Unauthorized - redirect to login
          // مدیریت 401 Unauthorized - هدایت به صفحه لاگین
          if (error?.response?.status === 401 || error?.response?.statusCode === 401) {
            toast.error("Please log in to create a booking");
            const currentPath = router.asPath;
            router.push(`/login?redirect=${encodeURIComponent(currentPath)}`);
            return;
          }

          const errs = error?.response?.data?.errors || [];
          const code = errs[0]?.code;
          const availableSlots = errs[0]?.available_slots;
          if (code === "slot_unavailable") {
            toast.error(
              availableSlots?.length
                ? `Selected slot unavailable. Available: ${availableSlots.join(", ")}`
                : "Selected slot is unavailable. Please pick another time."
            );
          } else if (code === "cancellation_window_passed") {
            toast.error("Booking not allowed within 24 hours of appointment time.");
          } else {
            toast.error(getBeautyErrorMessage(error) || "Failed to create booking");
          }
        },
      }
    );
  };

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
        <Typography variant="h4" fontWeight="bold">
          Create Booking
        </Typography>

        <Card>
          <CardContent>
            <Stepper activeStep={activeStep} alternativeLabel>
              {["Salon", "Service", "Schedule", "Payment"].map((label) => (
                <Step key={label}>
                  <StepLabel>{label}</StepLabel>
                </Step>
              ))}
            </Stepper>

            <form
              onSubmit={(e) => {
                if (activeStep < 3) {
                  e.preventDefault();
                  const next = handleStepValidation(activeStep);
                  if (next) setActiveStep((prev) => prev + 1);
                } else {
                  handleSubmit(e);
                }
              }}
            >
              <CustomStackFullWidth spacing={3}>
                {activeStep === 0 && (
                  <FormControl fullWidth>
                    <InputLabel>Salon</InputLabel>
                    <Select
                      value={formData.salon_id}
                      label="Salon"
                      onChange={(e) => {
                        setFormData({
                          ...formData,
                          salon_id: e.target.value,
                          service_id: "",
                          staff_id: "",
                        });
                      }}
                      required
                      disabled={salonsLoading}
                    >
                      {salons.map((s) => (
                        <MenuItem key={s.id} value={s.id}>
                          {s.name || s.store?.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                )}

                {activeStep === 1 && (
                  <>
                    <FormControl fullWidth>
                      <InputLabel>Service</InputLabel>
                      <Select
                        value={formData.service_id}
                        label="Service"
                          onChange={(e) => {
                            setFormData({ ...formData, service_id: e.target.value });
                          }}
                        required
                      >
                        {services.map((service) => (
                          <MenuItem key={service.id} value={service.id}>
                            {service.name} - ${service.price} ({service.duration_minutes} min)
                          </MenuItem>
                        ))}
                      </Select>
                    </FormControl>

                    {staff.length > 0 && (
                      <FormControl fullWidth>
                        <InputLabel>Staff (Optional)</InputLabel>
                        <Select
                          value={formData.staff_id}
                          label="Staff (Optional)"
                          onChange={(e) => {
                            setFormData({ ...formData, staff_id: e.target.value });
                          }}
                        >
                          <MenuItem value="">Any Staff</MenuItem>
                          {staff.map((staffMember) => (
                            <MenuItem key={staffMember.id} value={staffMember.id}>
                              {staffMember.name}
                            </MenuItem>
                          ))}
                        </Select>
                      </FormControl>
                    )}

                    {formData.service_id && (
                      <ServiceSuggestions
                        serviceId={formData.service_id}
                        salonId={formData.salon_id}
                        onAddToBooking={(suggestion) => {
                          toast.success(`${suggestion.name} added to booking`);
                        }}
                      />
                    )}
                  </>
                )}

                {activeStep === 2 && (
                  <AvailabilityCalendar
                    salonId={formData.salon_id}
                    serviceId={formData.service_id}
                    staffId={formData.staff_id}
                    selectedDate={selectedDate}
                    selectedTime={formData.booking_time}
                    onDateSelect={(date) => {
                      const normalized = date ? dayjs(date) : null;
                      setSelectedDate(normalized);
                      setFormData((prev) => ({
                        ...prev,
                        booking_date: normalized,
                        booking_time: "",
                      }));
                    }}
                    onTimeSelect={(time) => {
                      setFormData((prev) => ({ ...prev, booking_time: time }));
                    }}
                  />
                )}

                {activeStep === 3 && (
                  <>
                    <FormControl fullWidth>
                      <InputLabel>Payment Method</InputLabel>
                      <Select
                        value={formData.payment_method}
                        label="Payment Method"
                        onChange={(e) =>
                          setFormData({
                            ...formData,
                            payment_method: e.target.value,
                            payment_gateway:
                              e.target.value === "digital_payment"
                                ? formData.payment_gateway
                                : "",
                          })
                        }
                        required
                      >
                        <MenuItem value="cash_payment">Cash on Delivery</MenuItem>
                        <MenuItem value="wallet">Wallet</MenuItem>
                        <MenuItem value="digital_payment">Digital Payment</MenuItem>
                      </Select>
                    </FormControl>

                    {formData.payment_method === "digital_payment" && (
                      <FormControl fullWidth required>
                        <InputLabel>Payment Gateway</InputLabel>
                        <Select
                          value={formData.payment_gateway}
                          label="Payment Gateway"
                          onChange={(e) =>
                            setFormData({ ...formData, payment_gateway: e.target.value })
                          }
                        >
                          <MenuItem value="">Select Gateway</MenuItem>
                          {digitalGateways.map((gateway) => (
                            <MenuItem key={gateway.gateway} value={gateway.gateway}>
                              {gateway.gateway_title || gateway.gateway}
                            </MenuItem>
                          ))}
                        </Select>
                      </FormControl>
                    )}

                    <TextField
                      fullWidth
                      label="Notes (Optional)"
                      multiline
                      rows={3}
                      value={formData.notes}
                      onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    />
                  </>
                )}

                <Box display="flex" justifyContent="space-between" gap={2}>
                  <Button
                    variant="outlined"
                    onClick={() => setActiveStep((prev) => Math.max(0, prev - 1))}
                    disabled={activeStep === 0}
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    variant="contained"
                    color="primary"
                    disabled={isLoading}
                  >
                    {activeStep === 3 ? (isLoading ? "Creating..." : "Create Booking") : "Next"}
                  </Button>
                </Box>
              </CustomStackFullWidth>
            </form>
          </CardContent>
        </Card>
      </CustomStackFullWidth>
    </LocalizationProvider>
  );
};

export default BookingForm;

