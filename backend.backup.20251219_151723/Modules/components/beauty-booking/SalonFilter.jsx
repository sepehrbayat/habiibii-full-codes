import React, { useState, useEffect } from 'react';
import {
    Box,
    Card,
    CardContent,
    TextField,
    Select,
    MenuItem,
    FormControl,
    InputLabel,
    Button,
    Grid,
} from '@mui/material';
import { Search as SearchIcon } from '@mui/icons-material';
import axios from 'axios';

/**
 * Salon Filter Component
 * کامپوننت فیلتر سالن
 */
const SalonFilter = ({ filters, onFilterChange, apiBaseUrl, csrfToken }) => {
    const [categories, setCategories] = useState([]);
    const [localFilters, setLocalFilters] = useState(filters);

    useEffect(() => {
        // Fetch categories
        // دریافت دسته‌بندی‌ها
        const fetchCategories = async () => {
            try {
                const response = await axios.get(`${apiBaseUrl}/customer/salons/category-list`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                if (response.data && response.data.data) {
                    setCategories(response.data.data);
                }
            } catch (err) {
                console.error('Error fetching categories:', err);
            }
        };

        fetchCategories();
    }, []);

    const handleChange = (field, value) => {
        const newFilters = { ...localFilters, [field]: value };
        setLocalFilters(newFilters);
        onFilterChange(newFilters);
    };

    const handleSearch = () => {
        onFilterChange(localFilters);
    };

    return (
        <Card sx={{ mb: 3 }}>
            <CardContent>
                <Grid container spacing={2} alignItems="flex-end">
                    <Grid item xs={12} md={4}>
                        <TextField
                            fullWidth
                            label={window.translate?.('Search') || 'Search'}
                            placeholder={window.translate?.('Salon name...') || 'Salon name...'}
                            value={localFilters.search || ''}
                            onChange={(e) => handleChange('search', e.target.value)}
                            InputProps={{
                                startAdornment: <SearchIcon sx={{ mr: 1, color: 'text.secondary' }} />,
                            }}
                        />
                    </Grid>
                    <Grid item xs={12} md={3}>
                        <FormControl fullWidth>
                            <InputLabel>{window.translate?.('Category') || 'Category'}</InputLabel>
                            <Select
                                value={localFilters.category_id || ''}
                                onChange={(e) => handleChange('category_id', e.target.value)}
                                label={window.translate?.('Category') || 'Category'}
                            >
                                <MenuItem value="">
                                    {window.translate?.('All Categories') || 'All Categories'}
                                </MenuItem>
                                {categories.map((category) => (
                                    <MenuItem key={category.id} value={category.id}>
                                        {category.name}
                                    </MenuItem>
                                ))}
                            </Select>
                        </FormControl>
                    </Grid>
                    <Grid item xs={12} md={2}>
                        <FormControl fullWidth>
                            <InputLabel>{window.translate?.('Type') || 'Type'}</InputLabel>
                            <Select
                                value={localFilters.business_type || ''}
                                onChange={(e) => handleChange('business_type', e.target.value)}
                                label={window.translate?.('Type') || 'Type'}
                            >
                                <MenuItem value="">
                                    {window.translate?.('All') || 'All'}
                                </MenuItem>
                                <MenuItem value="salon">
                                    {window.translate?.('Salon') || 'Salon'}
                                </MenuItem>
                                <MenuItem value="clinic">
                                    {window.translate?.('Clinic') || 'Clinic'}
                                </MenuItem>
                            </Select>
                        </FormControl>
                    </Grid>
                    <Grid item xs={12} md={3}>
                        <FormControl fullWidth>
                            <InputLabel>{window.translate?.('Sort By') || 'Sort By'}</InputLabel>
                            <Select
                                value={localFilters.sort || 'rating'}
                                onChange={(e) => handleChange('sort', e.target.value)}
                                label={window.translate?.('Sort By') || 'Sort By'}
                            >
                                <MenuItem value="rating">
                                    {window.translate?.('Highest Rated') || 'Highest Rated'}
                                </MenuItem>
                                <MenuItem value="distance">
                                    {window.translate?.('Nearest') || 'Nearest'}
                                </MenuItem>
                                <MenuItem value="bookings">
                                    {window.translate?.('Most Popular') || 'Most Popular'}
                                </MenuItem>
                            </Select>
                        </FormControl>
                    </Grid>
                </Grid>
            </CardContent>
        </Card>
    );
};

export default SalonFilter;

