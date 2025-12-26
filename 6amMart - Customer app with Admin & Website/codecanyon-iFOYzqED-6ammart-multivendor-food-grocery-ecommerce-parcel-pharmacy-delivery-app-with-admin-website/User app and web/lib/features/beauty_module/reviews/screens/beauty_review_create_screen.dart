import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/beauty_module/reviews/controllers/beauty_review_controller.dart';
import 'package:sixam_mart/features/beauty_module/reviews/widgets/review_rating_widget.dart';

class BeautyReviewCreateScreen extends StatefulWidget {
  final int? bookingId;
  final int? salonId;
  final int? serviceId;

  const BeautyReviewCreateScreen({
    Key? key,
    this.bookingId,
    this.salonId,
    this.serviceId,
  }) : super(key: key);

  @override
  State<BeautyReviewCreateScreen> createState() => _BeautyReviewCreateScreenState();
}

class _BeautyReviewCreateScreenState extends State<BeautyReviewCreateScreen> {
  final _formKey = GlobalKey<FormState>();
  final _commentController = TextEditingController();
  final _bookingIdController = TextEditingController();
  
  int _rating = 5;
  bool _isLoading = false;

  @override
  void dispose() {
    _commentController.dispose();
    _bookingIdController.dispose();
    super.dispose();
  }

  Future<void> _submitReview() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);
    
    try {
      final bookingId = int.tryParse(_bookingIdController.text);
      if (bookingId == null) {
        showCustomSnackBar('Please enter a booking id');
        return;
      }

      await Get.find<BeautyReviewController>().submitReview(
        bookingId: bookingId,
        rating: _rating,
        comment: _commentController.text.trim(),
      );
      showCustomSnackBar('Review submitted successfully', isError: false);
      Get.back();
    } catch (e) {
      showCustomSnackBar('Failed to submit review');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Write a Review'),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Rate your experience',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              SizedBox(height: 20),
              
              // Rating stars
              ReviewRatingWidget(
                rating: _rating,
                onRatingTap: (value) => setState(() => _rating = value),
              ),
              
              SizedBox(height: 24),

              TextFormField(
                controller: _bookingIdController,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'Booking ID',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter a booking id';
                  }
                  return null;
                },
              ),

              SizedBox(height: 16),
              
              TextFormField(
                controller: _commentController,
                maxLines: 5,
                decoration: InputDecoration(
                  labelText: 'Your Review',
                  hintText: 'Tell us about your experience...',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please write a review';
                  }
                  return null;
                },
              ),
              
              SizedBox(height: 24),
              
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _submitReview,
                  child: _isLoading 
                      ? CircularProgressIndicator(color: Colors.white)
                      : Text('Submit Review'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
