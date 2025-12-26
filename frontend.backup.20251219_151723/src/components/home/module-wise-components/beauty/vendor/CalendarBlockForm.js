import React, { useState } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Button,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
} from "@mui/material";
import { toast } from "react-hot-toast";
import { useCreateCalendarBlock } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCreateCalendarBlock";

const CalendarBlockForm = ({ open, onClose, onSuccess }) => {
  const { mutate: createBlock, isLoading } = useCreateCalendarBlock();
  const [formData, setFormData] = useState({
    date: "",
    start_time: "",
    end_time: "",
    type: "manual_block",
    reason: "",
    staff_id: "",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    createBlock(
      {
        ...formData,
        staff_id: formData.staff_id || undefined,
      },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Block created successfully");
          if (onSuccess) onSuccess();
          onClose();
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to create block");
        },
      }
    );
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Create Calendar Block</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Date"
            name="date"
            type="date"
            value={formData.date}
            onChange={handleChange}
            fullWidth
            required
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            margin="dense"
            label="Start Time"
            name="start_time"
            type="time"
            value={formData.start_time}
            onChange={handleChange}
            fullWidth
            required
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            margin="dense"
            label="End Time"
            name="end_time"
            type="time"
            value={formData.end_time}
            onChange={handleChange}
            fullWidth
            required
            InputLabelProps={{ shrink: true }}
          />
          <FormControl fullWidth margin="dense">
            <InputLabel>Type</InputLabel>
            <Select
              name="type"
              value={formData.type}
              onChange={handleChange}
              required
            >
              <MenuItem value="break">Break</MenuItem>
              <MenuItem value="holiday">Holiday</MenuItem>
              <MenuItem value="manual_block">Manual Block</MenuItem>
            </Select>
          </FormControl>
          <TextField
            margin="dense"
            label="Reason"
            name="reason"
            value={formData.reason}
            onChange={handleChange}
            fullWidth
            multiline
            rows={3}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose}>Cancel</Button>
          <Button type="submit" variant="contained" disabled={isLoading}>
            {isLoading ? "Creating..." : "Create Block"}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
};

export default CalendarBlockForm;

