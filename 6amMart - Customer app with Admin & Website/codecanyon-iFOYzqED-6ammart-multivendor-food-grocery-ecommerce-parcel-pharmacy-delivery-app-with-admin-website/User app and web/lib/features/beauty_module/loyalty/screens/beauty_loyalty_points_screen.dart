import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/loyalty/controllers/beauty_loyalty_controller.dart';
import 'package:sixam_mart/features/beauty_module/loyalty/widgets/points_balance_widget.dart';

class BeautyLoyaltyPointsScreen extends StatelessWidget {
  const BeautyLoyaltyPointsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautyLoyaltyController>(
      initState: (_) => Get.find<BeautyLoyaltyController>().getLoyaltyPoints(),
      builder: (controller) {
        return Scaffold(
          appBar: AppBar(
            title: Text('Loyalty Points'),
          ),
          body: controller.isLoading
              ? Center(child: CircularProgressIndicator())
              : SingleChildScrollView(
                  padding: EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      PointsBalanceWidget(
                        points: controller.points ?? 0,
                      ),
                      SizedBox(height: 24),
                      Text(
                        'Recent Transactions',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      SizedBox(height: 16),
                      ListView.builder(
                        shrinkWrap: true,
                        physics: NeverScrollableScrollPhysics(),
                        itemCount: controller.transactions?.length ?? 0,
                        itemBuilder: (context, index) {
                          final transaction = controller.transactions![index];
                          return ListTile(
                            leading: Icon(
                              transaction['type'] == 'earned' 
                                  ? Icons.add_circle 
                                  : Icons.remove_circle,
                              color: transaction['type'] == 'earned' 
                                  ? Colors.green 
                                  : Colors.red,
                            ),
                            title: Text(transaction['description'] ?? ''),
                            subtitle: Text(transaction['date'] ?? ''),
                            trailing: Text(
                              (transaction['type'] == 'earned' ? "+" : "-") + (transaction['points']?.toString() ?? '0'),
                              style: TextStyle(
                                color: transaction['type'] == 'earned' 
                                    ? Colors.green 
                                    : Colors.red,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
        );
      },
    );
  }
}
