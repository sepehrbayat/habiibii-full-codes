import React, { useState } from "react";
import { Box, Button, Typography, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { toast } from "react-hot-toast";
import { useUploadDocuments } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useUploadDocuments";

const DocumentsUpload = () => {
  const { mutate: uploadDocuments, isLoading } = useUploadDocuments();
  const [files, setFiles] = useState([]);

  const handleFileChange = (e) => {
    setFiles(Array.from(e.target.files));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (files.length === 0) {
      toast.error("Please select at least one file");
      return;
    }
    uploadDocuments(files, {
      onSuccess: (res) => {
        toast.success(res?.message || "Documents uploaded successfully");
        setFiles([]);
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to upload documents");
      },
    });
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Upload Documents
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={2}>
          <input
            type="file"
            multiple
            onChange={handleFileChange}
            accept=".pdf,.jpg,.jpeg,.png"
          />

          {files.length > 0 && (
            <Typography variant="body2" color="text.secondary">
              {files.length} file(s) selected
            </Typography>
          )}

          <Button
            type="submit"
            variant="contained"
            color="primary"
            disabled={isLoading || files.length === 0}
          >
            {isLoading ? "Uploading..." : "Upload Documents"}
          </Button>
        </CustomStackFullWidth>
      </Box>
    </CustomStackFullWidth>
  );
};

export default DocumentsUpload;

