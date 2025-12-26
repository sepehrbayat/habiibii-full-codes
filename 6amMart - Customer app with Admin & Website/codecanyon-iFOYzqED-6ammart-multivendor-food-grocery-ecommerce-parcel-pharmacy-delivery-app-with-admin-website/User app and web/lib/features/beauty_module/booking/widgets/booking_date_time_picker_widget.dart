import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:intl/intl.dart';

class BookingDateTimePickerWidget extends StatefulWidget {
  final String? selectedDate;
  final String? selectedTime;
  final Function(String date, String time) onDateTimeSelected;
  
  const BookingDateTimePickerWidget({
    super.key,
    this.selectedDate,
    this.selectedTime,
    required this.onDateTimeSelected,
  });

  @override
  State<BookingDateTimePickerWidget> createState() => _BookingDateTimePickerWidgetState();
}

class _BookingDateTimePickerWidgetState extends State<BookingDateTimePickerWidget> {
  DateTime? _selectedDate;
  
  final List<String> _timeSlots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00', '20:30',
  ];

  @override
  void initState() {
    super.initState();
    if (widget.selectedDate != null) {
      try {
        _selectedDate = DateTime.parse(widget.selectedDate!);
      } catch (e) {
        _selectedDate = null;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Date Picker
        InkWell(
          onTap: () => _selectDate(context),
          child: Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              border: Border.all(
                color: Theme.of(context).primaryColor,
                width: 1,
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.calendar_today,
                      color: Theme.of(context).primaryColor,
                      size: 20,
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Text(
                      _selectedDate != null
                          ? DateFormat('EEEE, MMM d, yyyy').format(_selectedDate!)
                          : 'select_date'.tr,
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeDefault,
                        color: _selectedDate != null
                            ? Theme.of(context).textTheme.bodyLarge!.color
                            : Theme.of(context).hintColor,
                      ),
                    ),
                  ],
                ),
                Icon(
                  Icons.arrow_drop_down,
                  color: Theme.of(context).primaryColor,
                ),
              ],
            ),
          ),
        ),
        
        const SizedBox(height: Dimensions.paddingSizeDefault),
        
        // Time Slot Selection
        Container(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            border: Border.all(
              color: Theme.of(context).primaryColor,
              width: 1,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(
                    Icons.access_time,
                    color: Theme.of(context).primaryColor,
                    size: 20,
                  ),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  Text(
                    'select_time_slot'.tr,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              
              // Time slots grid
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 4,
                  childAspectRatio: 2.5,
                  crossAxisSpacing: Dimensions.paddingSizeSmall,
                  mainAxisSpacing: Dimensions.paddingSizeSmall,
                ),
                itemCount: _timeSlots.length,
                itemBuilder: (context, index) {
                  final timeSlot = _timeSlots[index];
                  final isSelected = widget.selectedTime == timeSlot;
                  final isDisabled = _isTimeSlotDisabled(timeSlot);
                  
                  return InkWell(
                    onTap: isDisabled ? null : () {
                      if (_selectedDate != null) {
                        widget.onDateTimeSelected(
                          DateFormat('yyyy-MM-dd').format(_selectedDate!),
                          timeSlot,
                        );
                      } else {
                        Get.snackbar(
                          'select_date_first'.tr,
                          'please_select_date_before_time'.tr,
                          backgroundColor: Theme.of(context).colorScheme.error,
                          colorText: Colors.white,
                        );
                      }
                    },
                    child: Container(
                      decoration: BoxDecoration(
                        color: isSelected
                            ? Theme.of(context).primaryColor
                            : isDisabled
                                ? Theme.of(context).disabledColor.withValues(alpha: 0.1)
                                : Theme.of(context).cardColor,
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        border: Border.all(
                          color: isSelected
                              ? Theme.of(context).primaryColor
                              : isDisabled
                                  ? Theme.of(context).disabledColor.withValues(alpha: 0.3)
                                  : Theme.of(context).primaryColor.withValues(alpha: 0.3),
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        timeSlot,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: isSelected
                              ? Colors.white
                              : isDisabled
                                  ? Theme.of(context).disabledColor
                                  : Theme.of(context).textTheme.bodyLarge!.color,
                        ),
                      ),
                    ),
                  );
                },
              ),
            ],
          ),
        ),
        
        // Selected DateTime Display
        if (_selectedDate != null && widget.selectedTime != null) ...[
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.check_circle,
                  color: Theme.of(context).primaryColor,
                  size: 20,
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: Text(
                    '${'selected'.tr}: ${DateFormat('MMM d, yyyy').format(_selectedDate!)} at ${widget.selectedTime}',
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: Theme.of(context).primaryColor,
              onPrimary: Colors.white,
              surface: Theme.of(context).cardColor,
              onSurface: Theme.of(context).textTheme.bodyLarge!.color!,
            ),
          ),
          child: child!,
        );
      },
    );
    
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      
      // Clear time selection when date changes
      if (widget.selectedTime != null) {
        widget.onDateTimeSelected(
          DateFormat('yyyy-MM-dd').format(picked),
          '',
        );
      }
    }
  }

  bool _isTimeSlotDisabled(String timeSlot) {
    if (_selectedDate == null) return false;
    
    // Disable past time slots for today
    if (_selectedDate!.day == DateTime.now().day &&
        _selectedDate!.month == DateTime.now().month &&
        _selectedDate!.year == DateTime.now().year) {
      final now = DateTime.now();
      final slotParts = timeSlot.split(':');
      final slotHour = int.parse(slotParts[0]);
      final slotMinute = int.parse(slotParts[1]);
      
      if (slotHour < now.hour || 
          (slotHour == now.hour && slotMinute <= now.minute)) {
        return true;
      }
    }
    
    return false;
  }
}
