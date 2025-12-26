import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/consultation/controllers/beauty_consultation_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyConsultationsScreen extends StatelessWidget {
  const BeautyConsultationsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautyConsultationController>(
      builder: (controller) {
        return Scaffold(
          appBar: AppBar(
            title: Text('Consultations'),
          ),
          body: controller.isLoading
              ? Center(child: CircularProgressIndicator())
              : controller.consultations == null || controller.consultations!.isEmpty
                  ? Center(child: Text('No consultations available'))
                  : ListView.builder(
                      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                      itemCount: controller.consultations!.length,
                      itemBuilder: (context, index) {
                        final consultation = controller.consultations![index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                          child: ListTile(
                            title: Text(consultation.consultantName ?? 'Consultant', style: robotoMedium),
                            subtitle: Text(consultation.scheduledAt ?? ''),
                            trailing: Text(consultation.status ?? ''),
                          ),
                        );
                      },
                    ),
        );
      },
    );
  }
}
