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
  CircularProgress,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { useBookConsultation } from "../../../../../api-manage/hooks/react-query/beauty/useBookConsultation";
import { useCheckConsultationAvailability } from "../../../../../api-manage/hooks/react-query/beauty/useCheckConsultationAvailability";
import useGetSalonDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetSalonDetails";
import useGetConsultations from "../../../../../api-manage/hooks/react-query/beauty/useGetConsultations";
import TimeSlotPicker from "./TimeSlotPicker";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import dayjs from "dayjs";

const ConsultationBooking = () => {
  const router = useRouter();
  const { salon_id, consultation_id } = router.query;
  const { data: salonData } = useGetSalonDetails(salon_id, !!salon_id);
  const { data: consultationsData } = useGetConsultations(
    { salon_id: salon_id || undefined },
    !!salon_id
  );
  const { mutate: checkAvailability, data: availabilityData, isLoading: checkingAvailability } = useCheckConsultationAvailability();
  const { mutate: bookConsultation, isLoading: isBooking } = useBookConsultation();

  const [formData, setFormData] = useState({
    salon_id: salon_id || "",
    consultation_id: consultation_id || "",
    staff_id: "",
    booking_date: null,
    booking_time: "",
    payment_method: "cash_payment",
    main_service_id: "",
    notes: "",
  });

  const salon = salonData?.data || salonData;
  const consultations = consultationsData?.data || [];
  const staff = salon?.staff || [];
  const availableSlots = availabilityData?.data?.available_slots || [];

  useEffect(() => {
    if (salon_id) {
      setFormData((prev) => ({ ...prev, salon_id }));
    }
    if (consultation_id) {
      setFormData((prev) => ({ ...prev, consultation_id }));
    }
  }, [salon_id, consultation_id]);

  const handleCheckAvailability = () => {
    if (!formData.salon_id || !formData.consultation_id || !formData.booking_date) {
      toast.error("Please fill in salon, consultation, and date");
      return;
    }

    checkAvailability({
      salon_id: formData.salon_id,
      consultation_id: formData.consultation_id,
      booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
      staff_id: formData.staff_id || undefined,
    });
  };

  const convertPaymentMethod = (value) =>
    value === "online" ? "digital_payment" : value;

  const isValidTime = (time) => /^\d{2}:\d{2}$/.test(time);

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!formData.salon_id || !formData.consultation_id || !formData.booking_date || !formData.booking_time) {
      toast.error("Please fill in all required fields");
      return;
    }

    if (!isValidTime(formData.booking_time)) {
      toast.error("Please select a valid booking time (HH:MM)");
      return;
    }

    const paymentMethod = convertPaymentMethod(formData.payment_method);

    const bookingPayload = {
      salon_id: formData.salon_id,
      consultation_id: formData.consultation_id,
      booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
      booking_time: formData.booking_time,
      payment_method: paymentMethod,
    };

    if (formData.staff_id) {
      bookingPayload.staff_id = formData.staff_id;
    }
    if (formData.main_service_id) {
      bookingPayload.main_service_id = formData.main_service_id;
    }
    if (formData.notes) {
      bookingPayload.notes = formData.notes;
    }

    bookConsultation(bookingPayload, {
      onSuccess: (response) => {
        toast.success("Consultation booked successfully");
        if (response?.data?.redirect_url) {
          window.location.href = response.data.redirect_url;
        } else {
          router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
        }
      },
      onError: (error) => {
        toast.error(getBeautyErrorMessage(error) || "Failed to book consultation");
      },
    });
  };

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
        <Typography variant="h4" fontWeight="bold">
          Book Consultation
        </Typography>

        <Card>
          <CardContent>
            <form onSubmit={handleSubmit}>
              <CustomStackFullWidth spacing={3}>
                {salon && (
                  <FormControl fullWidth>
                    <InputLabel>Salon</InputLabel>
                    <Select
                      value={formData.salon_id}
                      label="Salon"
                      onChange={(e) => setFormData({ ...formData, salon_id: e.target.value })}
                      required
                    >
                      <MenuItem value={salon.id}>{salon.name || salon.store?.name}</MenuItem>
                    </Select>
                  </FormControl>
                )}

                <FormControl fullWidth>
                  <InputLabel>Consultation</InputLabel>
                  <Select
                    value={formData.consultation_id}
                    label="Consultation"
                    onChange={(e) => {
                      setFormData({ ...formData, consultation_id: e.target.value });
                      if (formData.booking_date) {
                        setTimeout(() => handleCheckAvailability(), 100);
                      }
                    }}
                    required
                  >
                    {consultations.map((consultation) => (
                      <MenuItem key={consultation.id} value={consultation.id}>
                        {consultation.name || consultation.consultation_type} - ${consultation.price || 0}
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
                        if (formData.booking_date && formData.consultation_id) {
                          setTimeout(() => handleCheckAvailability(), 100);
                        }
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

                <DatePicker
                  label="Booking Date"
                  value={formData.booking_date ? dayjs(formData.booking_date) : null}
                  onChange={(date) => {
                    setFormData({ ...formData, booking_date: date });
                    if (date && formData.consultation_id) {
                      setTimeout(() => handleCheckAvailability(), 100);
                    }
                  }}
                  minDate={dayjs()}
                  slotProps={{
                    textField: {
                      fullWidth: true,
                      required: true,
                    },
                  }}
                />

                {checkingAvailability && (
                  <Box display="flex" justifyContent="center">
                    <CircularProgress size={24} />
                  </Box>
                )}

                {availableSlots.length > 0 && (
                  <TimeSlotPicker
                    slots={availableSlots}
                    value={formData.booking_time}
                    onChange={(time) => setFormData({ ...formData, booking_time: time })}
                  />
                )}

                <FormControl fullWidth>
                  <InputLabel>Payment Method</InputLabel>
                  <Select
                    value={formData.payment_method}
                    label="Payment Method"
                    onChange={(e) => setFormData({ ...formData, payment_method: e.target.value })}
                    required
                  >
                    <MenuItem value="cash_payment">Cash on Delivery</MenuItem>
                    <MenuItem value="wallet">Wallet</MenuItem>
                    <MenuItem value="digital_payment">Digital Payment</MenuItem>
                  </Select>
                </FormControl>

                <TextField
                  fullWidth
                  label="Main Service ID (Optional - for consultation credit)"
                  value={formData.main_service_id}
                  onChange={(e) => setFormData({ ...formData, main_service_id: e.target.value })}
                />

                <TextField
                  fullWidth
                  label="Notes (Optional)"
                  multiline
                  rows={3}
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                />

                <Button
                  type="submit"
                  variant="contained"
                  color="primary"
                  size="large"
                  fullWidth
                  disabled={isBooking || !formData.booking_time}
                >
                  {isBooking ? "Booking..." : "Book Consultation"}
                </Button>
              </CustomStackFullWidth>
            </form>
          </CardContent>
        </Card>
      </CustomStackFullWidth>
    </LocalizationProvider>
  );
};

export default ConsultationBooking;

