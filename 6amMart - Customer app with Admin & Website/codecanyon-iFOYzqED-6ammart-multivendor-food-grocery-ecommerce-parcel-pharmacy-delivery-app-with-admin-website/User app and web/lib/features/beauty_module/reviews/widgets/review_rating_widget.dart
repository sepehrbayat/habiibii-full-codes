import 'package:flutter/material.dart';

class ReviewRatingWidget extends StatelessWidget {
  final int rating;
  final int reviewCount;
  final Function(int)? onRatingTap;

  const ReviewRatingWidget({
    Key? key,
    required this.rating,
    this.reviewCount = 0,
    this.onRatingTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        ...List.generate(5, (index) {
          return GestureDetector(
            onTap: onRatingTap != null ? () => onRatingTap!(index + 1) : null,
            child: Icon(
              index < rating ? Icons.star : Icons.star_border,
              color: index < rating ? Colors.amber : Colors.grey,
              size: 20,
            ),
          );
        }),
        if (reviewCount > 0) ...[
          SizedBox(width: 8),
            Text(
              '($reviewCount)',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: Colors.grey,
            ),
          ),
        ],
      ],
    );
  }
}