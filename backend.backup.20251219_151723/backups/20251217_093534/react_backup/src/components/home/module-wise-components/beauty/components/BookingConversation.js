import React, { useState } from "react";
import { Card, CardContent, Typography, Box, Chip, CircularProgress, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useMutation } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";

const BookingConversation = ({ conversation, isLoading, bookingId, onRefresh }) => {
  const messages = conversation?.messages || [];
  const [message, setMessage] = useState("");
  const [file, setFile] = useState(null);

  const { mutate: sendMessage, isLoading: isSending } = useMutation(
    (payload) => BeautyApi.sendBookingMessage(bookingId, payload),
    {
      onSuccess: () => {
        toast.success("Message sent");
        setMessage("");
        setFile(null);
        if (onRefresh) onRefresh();
      },
      onError: (error) => {
        toast.error(getBeautyErrorMessage(error) || "Failed to send message");
      },
    }
  );

  const handleSend = () => {
    if (!message.trim() && !file) {
      toast.error("Please enter a message or attach a file");
      return;
    }

    let payload = { message };
    if (file) {
      const formData = new FormData();
      formData.append("message", message);
      formData.append("file", file);
      payload = formData;
    }

    sendMessage(payload);
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Typography variant="h6" fontWeight="bold">
            Conversation
          </Typography>

          {isLoading ? (
            <Box display="flex" justifyContent="center" p={2}>
              <CircularProgress size={24} />
            </Box>
          ) : messages.length > 0 ? (
            <CustomStackFullWidth spacing={1.5}>
              {messages.map((message) => (
                <Box
                  key={message.id}
                  sx={{
                    p: 2,
                    bgcolor: "background.default",
                    borderRadius: 1,
                    border: "1px solid",
                    borderColor: "divider",
                  }}
                >
                  <Box display="flex" justifyContent="space-between" alignItems="center" mb={0.5}>
                    <Typography variant="subtitle2" fontWeight="bold">
                      {message.sender_type || "User"}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                      {message.created_at}
                    </Typography>
                  </Box>
                  <Typography variant="body2" mb={0.5}>
                    {message.message || "-"}
                  </Typography>
                  {message.file && (
                    <Chip
                      label="Attachment"
                      size="small"
                      color="info"
                      onClick={() => window.open(message.file, "_blank")}
                    />
                  )}
                </Box>
              ))}
            </CustomStackFullWidth>
          ) : (
            <Typography variant="body2" color="text.secondary">
              No messages yet
            </Typography>
          )}

          {bookingId && (
            <CustomStackFullWidth spacing={1.5}>
              <TextField
                label="Message"
                fullWidth
                multiline
                rows={2}
                value={message}
                onChange={(e) => setMessage(e.target.value)}
              />
              <Button variant="outlined" component="label">
                {file ? "Change Attachment" : "Add Attachment"}
                <input
                  type="file"
                  hidden
                  onChange={(e) => setFile(e.target.files?.[0] || null)}
                />
              </Button>
              <Button
                variant="contained"
                onClick={handleSend}
                disabled={isSending}
              >
                {isSending ? "Sending..." : "Send Message"}
              </Button>
            </CustomStackFullWidth>
          )}
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default BookingConversation;

