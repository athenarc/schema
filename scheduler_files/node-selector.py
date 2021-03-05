#!/usr/bin/python3
####################################################################################
#
#  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
#  for the Information Management Systems Institute, "Athena" Research Center.
#  
#  This file is part of SCHeMa.
#  
#  SCHeMa is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  SCHeMa is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
#
####################################################################################
# from sklearn.datasets import make_classification
# from sklearn.model_selection import train_test_split
# from sklearn.linear_model import LogisticRegression
# from sklearn.svm import SVC
# from sklearn.ensemble import RandomForestClassifier
# from sklearn.model_selection import GridSearchCV
# from sklearn.metrics import classification_report
from sklearn import preprocessing
from sklearn.preprocessing import MinMaxScaler
# from sklearn import svm

# import csv
import sys
# import math
import numpy as np
import pickle
import subprocess

modelFileName=sys.argv[1]
scalerFileName=sys.argv[2]
jobFileName=sys.argv[3]

classes={0:'thin-node',1:'medium-node'}

# Read model from pickle file
modelFile=open(modelFileName,'rb')
model=pickle.load(modelFile)
modelFile.close()

scalerFile=open(scalerFileName,'rb')
scaler=pickle.load(scalerFile)
scalerFile.close()

# Read feature values from file
jobFile=open(jobFileName,'r')
lines=[]
for line in jobFile:
    line=line.strip()
    if line!='':
        lines.append(line)


# print(lines)
jobFile.close()
if len(lines)>1:
    exit(10)
line=lines[0].split('|')
features=[]
for feature in line:
    if feature.isnumeric():
        features.append(int(feature))

features=[features]

# Transform arrays in numpy format
X = np.array(features)

#Scale according to the paramters calculated during model training
X_std = (X - scaler['min']) / (scaler['max'] - scaler['min'])
X= X_std * (1 - 0) + 0


result=model.predict(X)
nodeType=classes[result[0]]
print(nodeType)




