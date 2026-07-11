import os

path = r"c:\xampp\htdocs\calibration certificate\certificates\full_lab.php"

with open(path, "r", encoding="utf-8") as f:
    lines = f.readlines()

# Verify the boundary lines (0-indexed, so line 1157 is index 1156, line 2058 is index 2057)
print("Start boundary line (index 1156):", repr(lines[1156]))
print("End boundary line (index 2057):", repr(lines[2057]))

replacement = """                ];

                doc.autoTable({
                        head: [['SR.NO', 'STANDARD WEIGHTS', 'WEIGHT SHOWN BY 1 ST BUCKET','WEIGHT SHOWN BY 2ND BUCKET']],
                        body: data,
                        startY: tableStartY + 10,
                        styles: { 
                        fontSize: 12 ,
                        lineColor:[87, 86, 85],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                        },
                        headStyles: {
                        fontSize: 15,
                        fillColor: [255, 255, 255],
                        textColor: [0,0,0],
                        lineColor: [0, 0, 0],
                        lineWidth: 0.2,
                        halign: 'center',
                        valign: 'middle',
                        },
                        alternateRowStyles: {
                        fillColor: [255, 255, 255]
                        }
                    });
                    let tableStartY2=doc.autoTable.previous.finalY;
                // Add calibrated by
                doc.setFontSize(12);
                doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2+=10);

                // Add footer
                doc.setFont("helvetica", "BOLD"); 
                doc.setFontSize(12); 
                doc.text("FOR, Shreeji Instruments", 145, 230);
                doc.text("Proprietor", 170, 245);

            }    
        }
"""

new_lines = lines[:1156] + [replacement] + lines[2058:]

with open(path, "w", encoding="utf-8") as f:
    f.writelines(new_lines)

print("Replacement complete!")
