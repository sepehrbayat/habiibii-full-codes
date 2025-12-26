import React, { useMemo, useState } from "react";
import {
  Box,
  Button,
  Card,
  CardContent,
  CircularProgress,
  Typography,
  Chip,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useQuery, useMutation } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import { useRouter } from "next/router";

const BeautyNotifications = () => {
  const router = useRouter();
  const [enabled, setEnabled] = useState(false);

  const {
    data,
    isLoading,
    refetch,
    isFetching,
    isRefetching,
  } = useQuery(
    ["beauty-notifications"],
    async () => {
      const response = await BeautyApi.getNotifications();
      return response.data;
    },
    { enabled }
  );

  const notifications = data?.data || [];
  const unreadCount = data?.unread_count || 0;

  const markReadMutation = useMutation((ids) =>
    BeautyApi.markReadNotifications(ids)
  );

  const handleCardClick = async (notification) => {
    const bookingId =
      notification?.booking_id ||
      notification?.bookingID ||
      notification?.reference_id ||
      notification?.data?.booking_id;

    if (notification?.id) {
      try {
        await markReadMutation.mutateAsync([notification.id]);
        refetch();
      } catch (e) {
        // ignore
      }
    }

    if (bookingId) {
      router.push(`/beauty/bookings/${bookingId}`);
    }
  };

  const markAllReadMutation = useQuery(
    ["beauty-notifications-mark-read"],
    async () => {
      const ids = notifications.map((n) => n.id).filter(Boolean);
      if (ids.length === 0) return { data: {} };
      return BeautyApi.markReadNotifications(ids);
    },
    { enabled: false }
  );

  const handleMarkAllRead = async () => {
    await markAllReadMutation.refetch();
    refetch();
  };

  const headerChip = useMemo(
    () =>
      unreadCount > 0 ? (
        <Chip label={`${unreadCount} unread`} color="primary" size="small" />
      ) : (
        <Chip label="All read" size="small" />
      ),
    [unreadCount]
  );

  return (
    <CustomStackFullWidth spacing={2} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h5" fontWeight="bold">
          Notifications
        </Typography>
        {headerChip}
        <Button
          variant="contained"
          onClick={() => {
            if (!enabled) setEnabled(true);
            refetch();
          }}
          disabled={isFetching || isRefetching}
        >
          Refresh
        </Button>
      </Box>

      <Box display="flex" gap={1}>
        <Button
          variant="outlined"
          size="small"
          onClick={handleMarkAllRead}
          disabled={markAllReadMutation.isFetching || notifications.length === 0}
        >
          Mark all as read
        </Button>
      </Box>

      {isLoading || isFetching ? (
        <Box display="flex" justifyContent="center" p={3}>
          <CircularProgress />
        </Box>
      ) : notifications.length > 0 ? (
        <CustomStackFullWidth spacing={1.5}>
          {notifications.map((notification) => (
            <Card
              key={notification.id}
              onClick={() => handleCardClick(notification)}
              sx={{ cursor: "pointer" }}
            >
              <CardContent>
                <Typography variant="subtitle1" fontWeight="bold">
                  {notification.title || "Notification"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {notification.body || notification.message || ""}
                </Typography>
                {notification.created_at && (
                  <Typography variant="caption" color="text.secondary">
                    {notification.created_at}
                  </Typography>
                )}
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Typography variant="body2" color="text.secondary">
          No notifications yet.
        </Typography>
      )}
    </CustomStackFullWidth>
  );
};

export default BeautyNotifications;

